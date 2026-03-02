<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::forUser($user)
            ->with(['listing.media', 'buyer', 'seller', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('conversations.index', compact('conversations'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['listing.media', 'buyer', 'seller', 'messages.sender']);
        $conversation->markAsReadFor($request->user());

        return view('conversations.show', compact('conversation'));
    }

    public function store(Request $request, Listing $listing)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        if ($listing->user_id === $user->id) {
            return back()->with('error', __('messages.cannot_message_own_listing'));
        }

        if (!$listing->isActive()) {
            return back()->with('error', __('messages.listing_not_available'));
        }

        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $listing->user_id,
            ]
        );

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor($user);

        return redirect()->route('conversations.show', $conversation)
            ->with('success', __('messages.message_sent'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $this->authorize('sendMessage', $conversation);

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor($request->user());

        return back()->with('success', __('messages.message_sent'));
    }
}
