<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Liste des paiements de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $payments = $request->user()
            ->payments()
            ->with(['listing', 'subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($payments);
    }

    /**
     * Paiement pour la publication d'une annonce.
     */
    public function storeListingPayment(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if (! in_array($listing->status, ['awaiting_payment', 'draft'])) {
            return response()->json([
                'message' => 'Cette annonce a déjà été payée ou n\'est pas éligible au paiement.',
            ], 422);
        }

        $validated = $request->validate([
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $amount = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;

        $proofPath = $request->file('proof')->store('payment-proofs', $this->proofDisk());

        $payment = Payment::create([
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

        return response()->json([
            'message' => 'Votre preuve de paiement a été soumise avec succès. Elle sera vérifiée par un administrateur.',
            'payment' => $payment,
        ], 201);
    }

    /**
     * Paiement pour la mise en avant d'une annonce.
     */
    public function storeFeaturePayment(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return response()->json([
                'message' => 'L\'annonce doit être active pour pouvoir être mise en avant.',
            ], 422);
        }

        $validated = $request->validate([
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', $this->proofDisk());

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'type' => 'featured_listing',
            'amount_dzd' => 12000,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Votre paiement pour la mise en avant a été soumis avec succès.',
            'payment' => $payment,
        ], 201);
    }

    /**
     * Paiement pour un abonnement vendeur.
     */
    public function storeSubscriptionPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', $this->proofDisk());

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'subscription_id' => $subscription->id,
            'type' => 'vendor_subscription',
            'amount_dzd' => $plan->price_dzd,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Votre paiement d\'abonnement a été soumis avec succès.',
            'payment' => $payment,
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * Get the configured disk name for payment proof storage.
     */
    protected function proofDisk(): string
    {
        return config('filesystems.listing_disk', 'public');
    }

    /**
     * Paiement des frais de médiation.
     */
    public function storeMediationPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:mediation_tickets,id',
            'method' => 'required|in:baridimob,ccp,bank_transfer,paypal,redotpay,card',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $ticket = MediationTicket::findOrFail($validated['ticket_id']);

        if ($ticket->buyer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à effectuer ce paiement.',
            ], 403);
        }

        if ($ticket->payment_status !== 'unpaid') {
            return response()->json([
                'message' => 'Les frais de médiation pour ce ticket ont déjà été payés.',
            ], 422);
        }

        $existingPayment = Payment::where('mediation_ticket_id', $ticket->id)
            ->where('type', 'mediation_fee')
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existingPayment) {
            return response()->json([
                'message' => 'Un paiement pour ce ticket de médiation est déjà en cours de traitement.',
            ], 422);
        }

        $proofPath = $request->file('proof')->store('payment-proofs', $this->proofDisk());

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'mediation_ticket_id' => $ticket->id,
            'type' => 'mediation_fee',
            'amount_dzd' => 500,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Votre paiement des frais de médiation a été soumis avec succès.',
            'payment' => $payment,
        ], 201);
    }

    // ─── STRIPE (Mobile : iOS / Android) ────────────────────────────────────
    //
    // Flow recommandé côté mobile :
    //   1. POST /api/v1/payments/stripe/listing/{listing}
    //      → reçoit { url, session_id }
    //   2. Ouvrir `url` dans SFSafariViewController (iOS) ou Custom Tabs (Android).
    //   3. Stripe redirige vers la `success_url` / `cancel_url` web — l'app
    //      ferme la WebView et appelle GET /api/v1/payments/stripe/session/{id}
    //      pour vérifier que le paiement est bien confirmé.
    //   4. Le webhook serveur (POST /stripe/webhook) active le paiement en
    //      parallèle ; les deux chemins sont idempotents.

    /**
     * Crée une session Stripe Checkout pour la publication d'une annonce.
     */
    public function stripeCheckoutListing(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if (! in_array($listing->status, ['awaiting_payment', 'draft'])) {
            return response()->json([
                'message' => 'Cette annonce a déjà été payée ou n\'est pas éligible au paiement.',
            ], 422);
        }

        // Si un paiement Stripe est déjà approuvé pour cette annonce, on bloque.
        $alreadyApproved = Payment::where('listing_id', $listing->id)
            ->where('method', 'stripe')
            ->where('status', 'approved')
            ->exists();
        if ($alreadyApproved) {
            return response()->json(['message' => 'Cette annonce est déjà payée.'], 422);
        }

        $secret = config('services.stripe.secret');
        if (! $secret) {
            Log::error('Stripe API: STRIPE_SECRET not configured');
            return response()->json(['message' => 'Stripe n\'est pas configuré côté serveur.'], 503);
        }

        $amountDzd  = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;
        $rate       = (float) (Setting::where('key', 'exchange_rate_eur_dzd')->value('value') ?: 238);
        $amountEur  = $rate > 0 ? round($amountDzd / $rate, 2) : 21.00;
        $amountCents = max(50, (int) ($amountEur * 100)); // Stripe minimum : 50 cents

        try {
            $client   = new \GuzzleHttp\Client(['timeout' => 30, 'verify' => true]);
            $response = $client->post('https://api.stripe.com/v1/checkout/sessions', [
                'auth'        => [$secret, ''],
                'form_params' => [
                    'payment_method_types[0]'              => 'card',
                    'line_items[0][price_data][currency]'   => 'eur',
                    'line_items[0][price_data][unit_amount]' => $amountCents,
                    'line_items[0][price_data][product_data][name]'        => 'Publication AlBabor — ' . $listing->title,
                    'line_items[0][price_data][product_data][description]' => 'Frais de publication (valable 365 jours)',
                    'line_items[0][quantity]'               => 1,
                    'mode'                                  => 'payment',
                    'success_url'                           => route('payments.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'                            => route('listings.show', $listing),
                    'metadata[listing_id]'                  => $listing->id,
                    'metadata[user_id]'                     => $request->user()->id,
                    'metadata[type]'                        => 'publish_listing',
                    'metadata[source]'                      => 'mobile_api',
                    'customer_email'                        => $request->user()->email,
                ],
            ]);

            $session = json_decode($response->getBody(), true);
        } catch (\Throwable $e) {
            Log::error('Stripe API checkout failed', [
                'listing_id' => $listing->id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Impossible de créer la session Stripe.'], 502);
        }

        // Enregistre un paiement pending idempotent.
        $payment = Payment::updateOrCreate(
            ['listing_id' => $listing->id, 'method' => 'stripe', 'status' => 'pending'],
            [
                'user_id'           => $request->user()->id,
                'type'              => 'publish_listing',
                'amount_dzd'        => $amountDzd,
                'stripe_session_id' => $session['id'],
            ]
        );

        return response()->json([
            'url'        => $session['url'],
            'session_id' => $session['id'],
            'amount_eur' => $amountEur,
            'amount_dzd' => $amountDzd,
            'payment_id' => $payment->id,
        ], 201);
    }

    /**
     * Vérifie l'état d'une session Stripe (polling depuis l'app mobile
     * après fermeture de la WebView).
     */
    public function stripeSessionStatus(Request $request, string $sessionId): JsonResponse
    {
        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Si déjà approuvé localement (par le webhook), on répond directement.
        if ($payment->status === 'approved') {
            return response()->json([
                'status'     => 'approved',
                'payment'    => $payment,
                'listing_id' => $payment->listing_id,
            ]);
        }

        $secret = config('services.stripe.secret');
        if (! $secret) {
            return response()->json(['message' => 'Stripe n\'est pas configuré côté serveur.'], 503);
        }

        // Sinon on demande à Stripe.
        try {
            $client   = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}", [
                'auth' => [$secret, ''],
            ]);
            $session = json_decode($response->getBody(), true);
        } catch (\Throwable $e) {
            Log::error('Stripe API session fetch failed', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Impossible de vérifier le paiement Stripe.'], 502);
        }

        $stripeStatus = $session['payment_status'] ?? null;

        if ($stripeStatus === 'paid' && $payment->status === 'pending') {
            $payment->update([
                'status'                 => 'approved',
                'approved_at'            => now(),
                'stripe_payment_intent'  => $session['payment_intent'] ?? null,
            ]);

            $listing = $payment->listing;
            if ($listing && $payment->type === 'publish_listing') {
                $listing->update([
                    'status'          => 'pending_review',
                    'published_until' => now()->addDays(365),
                ]);
            }
        }

        return response()->json([
            'status'        => $payment->fresh()->status,
            'stripe_status' => $stripeStatus,
            'payment'       => $payment->fresh(),
            'listing_id'    => $payment->listing_id,
        ]);
    }
}
