<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
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
            'method' => 'required|in:baridimob,bank_transfer,paypal',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $amount = in_array($listing->category, ['boat', 'jetski']) ? 5000 : 0;

        // Store proof image
        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

        Payment::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'type' => 'publish_listing',
            'amount_dzd' => $amount,
            'method' => $validated['method'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('listings.my')
            ->with('success', __('messages.payment_submitted'));
    }

    public function storeFeaturePayment(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return back()->with('error', __('messages.listing_must_be_active'));
        }

        $validated = $request->validate([
            'method' => 'required|in:baridimob,bank_transfer,paypal',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
            'method' => 'required|in:baridimob,bank_transfer,paypal',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        // Create pending subscription
        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
            'method' => 'required|in:baridimob,bank_transfer,paypal',
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

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

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
}
