<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\MediationTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $buyerTickets = $user->buyerTickets()
            ->with(['listing.media', 'seller'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sellerTickets = $user->sellerTickets()
            ->with(['listing.media', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mediation.index', compact('buyerTickets', 'sellerTickets'));
    }

    public function create(Listing $listing)
    {
        // Check if mediation is enabled for this listing
        if (!$listing->mediation_enabled) {
            return back()->with('error', __('messages.mediation_not_available'));
        }

        // Check if listing is active
        if ($listing->status !== 'active') {
            return back()->with('error', __('messages.listing_not_available'));
        }

        // Check if user is not the owner
        if ($listing->user_id === Auth::id()) {
            return back()->with('error', __('messages.cannot_mediate_own_listing'));
        }

        // Check if there's already an open ticket
        $existingTicket = MediationTicket::where('listing_id', $listing->id)
            ->where('buyer_id', Auth::id())
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('mediation.show', $existingTicket)
                ->with('info', __('messages.existing_ticket'));
        }

        return view('mediation.create', compact('listing'));
    }

    public function store(Request $request, Listing $listing)
    {
        // Validate
        if (!$listing->mediation_enabled || $listing->status !== 'active') {
            return back()->with('error', __('messages.mediation_not_available'));
        }

        if ($listing->user_id === Auth::id()) {
            return back()->with('error', __('messages.cannot_mediate_own_listing'));
        }

        // Check if there's already an open ticket for this listing by the same buyer
        $existingTicket = MediationTicket::where('listing_id', $listing->id)
            ->where('buyer_id', Auth::id())
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('mediation.show', $existingTicket)
                ->with('error', __('messages.existing_ticket'));
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket = MediationTicket::create([
            'listing_id' => $listing->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $listing->user_id,
            'status' => 'new',
            'messages' => [
                [
                    'user_id' => Auth::id(),
                    'message' => $validated['message'],
                    'created_at' => now()->toIso8601String(),
                ]
            ],
        ]);

        return redirect()->route('mediation.show', $ticket)
            ->with('success', __('messages.ticket_created'));
    }

    public function show(MediationTicket $ticket)
    {
        $user = Auth::user();

        // Only buyer, seller, or admin can view
        if ($ticket->buyer_id !== $user->id && $ticket->seller_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $ticket->load(['listing.media', 'buyer', 'seller']);

        return view('mediation.show', compact('ticket'));
    }

    public function addMessage(Request $request, MediationTicket $ticket)
    {
        $user = Auth::user();

        // Only buyer, seller, or admin can add messages
        if ($ticket->buyer_id !== $user->id && $ticket->seller_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        // Cannot add messages to completed or cancelled tickets
        if (in_array($ticket->status, ['closed', 'cancelled'])) {
            return back()->with('error', __('messages.ticket_closed'));
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $messages = $ticket->messages ?? [];
        $messages[] = [
            'user_id' => $user->id,
            'message' => $validated['message'],
            'created_at' => now()->toIso8601String(),
        ];

        $ticket->update(['messages' => $messages]);

        return back()->with('success', __('messages.message_sent'));
    }

    public function cancel(MediationTicket $ticket)
    {
        $user = Auth::user();

        // Only buyer can cancel
        if ($ticket->buyer_id !== $user->id) {
            abort(403);
        }

        if ($ticket->status !== 'new') {
            return back()->with('error', __('messages.cannot_cancel_ticket'));
        }

        $ticket->update(['status' => 'cancelled']);

        return redirect()->route('mediation.index')
            ->with('success', __('messages.ticket_cancelled'));
    }
}
