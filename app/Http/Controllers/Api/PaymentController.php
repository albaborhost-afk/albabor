<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'method' => 'required|in:baridimob,ccp,bank_transfer',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $amount = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'type' => 'publish_listing',
            'amount_dzd' => $amount,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

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
            'method' => 'required|in:baridimob,ccp,bank_transfer',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
            'method' => 'required|in:baridimob,ccp,bank_transfer',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
     * Paiement des frais de médiation.
     */
    public function storeMediationPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:mediation_tickets,id',
            'method' => 'required|in:baridimob,ccp,bank_transfer',
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

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
}
