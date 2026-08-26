<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook as StripeWebhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    private function proofDisk(): string
    {
        return config('filesystems.listing_disk', 'public');
    }

    private function storeProof(Request $request): string|false
    {
        try {
            $path = $request->file('proof')->store('payment-proofs', $this->proofDisk());
            return $path ?: false;
        } catch (\Throwable $e) {
            Log::error('Payment proof storage failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function index()
    {
        $payments = Auth::user()
            ->payments()
            ->with(['listing', 'subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function storeListingPayment(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        if (!in_array($listing->status, ['awaiting_payment', 'draft'])) {
            return back()->with('error', __('messages.listing_already_paid'));
        }

        $validated = $request->validate([
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $amount = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;

        $proofPath = $this->storeProof($request);
        if ($proofPath === false) {
            return back()->withInput()->with('error', 'Erreur lors du téléversement du justificatif. Veuillez réessayer.');
        }

        Payment::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'type' => 'publish_listing',
            'amount_dzd' => $amount,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        // Move listing to pending_review so user sees payment is under review
        $listing->update(['status' => 'pending_review']);

        return redirect()->route('listings.my')
            ->with('success', __('messages.payment_submitted'))
            ->with('listing_created', true);
    }

    public function storeFeaturePayment(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return back()->with('error', __('messages.listing_must_be_active'));
        }

        $validated = $request->validate([
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $proofPath = $this->storeProof($request);
        if ($proofPath === false) {
            return back()->withInput()->with('error', 'Erreur lors du téléversement du justificatif. Veuillez réessayer.');
        }

        Payment::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'type' => 'featured_listing',
            'amount_dzd' => 12000,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('listings.my')
            ->with('success', __('messages.feature_payment_submitted'));
    }

    public function subscriptionPlans()
    {
        $plans = Plan::active()->orderBy('price_dzd')->get();
        $activeSubscription = Auth::user()->activeSubscription;

        return view('payments.subscription', compact('plans', 'activeSubscription'));
    }

    public function storeSubscriptionPayment(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        // Create pending subscription
        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $proofPath = $this->storeProof($request);
        if ($proofPath === false) {
            return back()->withInput()->with('error', 'Erreur lors du téléversement du justificatif. Veuillez réessayer.');
        }

        Payment::create([
            'user_id' => Auth::id(),
            'subscription_id' => $subscription->id,
            'type' => 'vendor_subscription',
            'amount_dzd' => $plan->price_dzd,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('profile.show')
            ->with('success', __('messages.subscription_payment_submitted'));
    }

    public function storeMediationPayment(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:mediation_tickets,id',
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $ticket = MediationTicket::findOrFail($validated['ticket_id']);

        // Verify the authenticated user is the buyer of this ticket
        if ($ticket->buyer_id !== Auth::id()) {
            abort(403);
        }

        // Verify the ticket payment is still unpaid
        if ($ticket->payment_status !== 'unpaid') {
            return back()->with('error', __('messages.mediation_already_paid'));
        }

        // Prevent duplicate pending/approved payments for the same ticket
        $existingPayment = Payment::where('mediation_ticket_id', $ticket->id)
            ->where('type', 'mediation_fee')
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existingPayment) {
            return back()->with('error', __('messages.mediation_payment_already_submitted'));
        }

        $proofPath = $this->storeProof($request);
        if ($proofPath === false) {
            return back()->withInput()->with('error', 'Erreur lors du téléversement du justificatif. Veuillez réessayer.');
        }

        Payment::create([
            'user_id' => Auth::id(),
            'mediation_ticket_id' => $ticket->id,
            'type' => 'mediation_fee',
            'amount_dzd' => 500, // Mediation fee
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('mediation.index')
            ->with('success', __('messages.mediation_payment_submitted'));
    }

    // ─── STRIPE ─────────────────────────────────────────────────────────────

    public function stripeCheckout(Listing $listing)
    {
        $this->authorize('update', $listing);

        if (!in_array($listing->status, ['awaiting_payment', 'draft'])) {
            return redirect()->route('listings.my')
                ->with('error', __('messages.listing_already_paid'));
        }

        // Check for existing pending/approved Stripe payment
        $existing = Payment::where('listing_id', $listing->id)
            ->where('method', 'stripe')
            ->whereIn('status', ['pending', 'approved'])
            ->first();
        if ($existing && $existing->status === 'approved') {
            return redirect()->route('listings.my')
                ->with('info', 'Votre annonce est déjà payée.');
        }

        $amountDzd = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;

        // Convert DZD → EUR using platform exchange rate
        $rate = (float) (Setting::where('key', 'exchange_rate_eur_dzd')->value('value') ?: 238);
        $amountEur = $rate > 0 ? round($amountDzd / $rate, 2) : 0.0;
        $amountCents = (int) round($amountEur * 100);

        // Stripe refuse toute charge sous 0,50 €. L'ancien `max(50, …)` forçait
        // le montant à 50 centimes au lieu de s'arrêter : une annonce gratuite
        // (moteur, pièce) se serait fait facturer, un taux de change cassé aussi.
        if ($amountCents < 50) {
            return back()->with('error', 'Le paiement en ligne n\'est pas disponible pour ce montant. Utilisez un virement.');
        }

        try {
            // Use Guzzle directly — Stripe SDK cURL client fails on Laravel Cloud
            $client = new \GuzzleHttp\Client(['timeout' => 30, 'verify' => true]);
            $response = $client->post('https://api.stripe.com/v1/checkout/sessions', [
                'auth' => [config('services.stripe.secret'), ''],
                'form_params' => [
                    // `payment_method_types` n'est volontairement PAS envoyé.
                    // Le fixer à « card » limitait la page Stripe à la saisie
                    // d'un numéro de carte : ni Apple Pay, ni Google Pay, ni
                    // Link n'apparaissaient. Sans ce paramètre, Stripe propose
                    // tout ce qui est activé dans le tableau de bord et que
                    // l'appareil du visiteur sait présenter.
                    'line_items[0][price_data][currency]'   => 'eur',
                    'line_items[0][price_data][unit_amount]' => $amountCents,
                    'line_items[0][price_data][product_data][name]' => 'Publication AlBabor — ' . $listing->title,
                    'line_items[0][price_data][product_data][description]' => 'Frais de publication (valable 365 jours)',
                    'line_items[0][quantity]'               => 1,
                    'mode'                                  => 'payment',
                    'success_url'                           => route('payments.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'                            => route('listings.payment', $listing),
                    'metadata[listing_id]'                  => $listing->id,
                    'metadata[user_id]'                     => Auth::id(),
                    'metadata[type]'                        => 'publish_listing',
                    'customer_email'                        => Auth::user()->email,
                ],
            ]);

            $session = json_decode($response->getBody(), true);

            // Create pending payment record
            Payment::updateOrCreate(
                ['listing_id' => $listing->id, 'method' => 'stripe', 'status' => 'pending'],
                [
                    'user_id'           => Auth::id(),
                    'type'              => 'publish_listing',
                    'amount_dzd'        => $amountDzd,
                    'stripe_session_id' => $session['id'],
                ]
            );

            return redirect($session['url'], 303);
        } catch (\Exception $e) {
            Log::error('Stripe checkout failed', [
                'listing_id' => $listing->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur Stripe : ' . $e->getMessage());
        }
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('listings.my')->with('error', 'Session de paiement invalide.');
        }

        try {
            // Use Guzzle — Stripe SDK cURL client fails on Laravel Cloud
            $client = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}", [
                'auth' => [config('services.stripe.secret'), ''],
            ]);
            $session = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Stripe session retrieval failed', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            return redirect()->route('listings.my')->with('error', 'Impossible de vérifier le paiement Stripe.');
        }

        if (($session['payment_status'] ?? '') !== 'paid') {
            return redirect()->route('listings.my')
                ->with('error', 'Le paiement Stripe n\'a pas été complété.');
        }

        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (!$payment) {
            Log::error('Stripe success: payment record not found', ['session_id' => $sessionId]);
            return redirect()->route('listings.my')
                ->with('error', 'Paiement introuvable — contactez le support.');
        }

        if ($payment->status === 'approved') {
            return redirect()->route('listings.my')
                ->with('success', 'Paiement déjà confirmé — votre annonce est en cours de vérification.');
        }

        $this->activateStripePayment($payment, $session['payment_intent'] ?? null);

        return redirect()->route('listings.my')
            ->with('success', '✅ Paiement Stripe confirmé ! Votre annonce est en cours de vérification par notre équipe.');
    }

    public function stripeWebhook(Request $request)
    {
        $payload       = $request->getContent();
        $sigHeader     = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        // SECURITY: Le secret est obligatoire. Sans lui, n'importe qui pourrait
        // forger un évènement "checkout.session.completed" et activer un paiement.
        if (! $webhookSecret) {
            Log::error('Stripe webhook: STRIPE_WEBHOOK_SECRET not configured — webhook refused');
            return response('Webhook secret not configured', 500);
        }

        if (! $sigHeader) {
            Log::warning('Stripe webhook: missing Stripe-Signature header');
            return response('Missing signature header', 400);
        }

        try {
            $event = StripeWebhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        }

        $type = is_object($event) ? $event->type : ($event['type'] ?? '');

        if ($type === 'checkout.session.completed') {
            $session = is_object($event) ? $event->data->object : ($event['data']['object'] ?? null);
            if (!$session) return response('OK', 200);

            $sessionId = is_object($session) ? $session->id : ($session['id'] ?? null);
            $paymentIntent = is_object($session) ? ($session->payment_intent ?? null) : ($session['payment_intent'] ?? null);

            $payment = Payment::where('stripe_session_id', $sessionId)->first();
            if ($payment && $payment->status === 'pending') {
                $this->activateStripePayment($payment, $paymentIntent);
            }
        }

        return response('OK', 200);
    }

    private function activateStripePayment(Payment $payment, ?string $paymentIntent): void
    {
        $payment->update([
            'status'                 => 'approved',
            'approved_at'            => now(),
            'stripe_payment_intent'  => $paymentIntent,
        ]);

        $listing = $payment->listing;
        if ($listing && $payment->type === 'publish_listing') {
            $listing->update([
                'status'           => 'pending_review',
                'published_until'  => now()->addDays(365),
            ]);
        }

        Log::info('Stripe payment activated', [
            'payment_id' => $payment->id,
            'listing_id' => $payment->listing_id,
        ]);
    }
}
