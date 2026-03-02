<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $conversation->unread_count = $conversation->unreadCountFor($user);
            $conversation->role = $conversation->isBuyer($user) ? 'buyer' : 'seller';
            return $conversation;
        });

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['listing.media', 'buyer', 'seller', 'messages.sender']);
        $conversation->markAsReadFor($request->user());

        return response()->json($conversation);
    }

    public function store(Request $request, Listing $listing)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        if ($listing->user_id === $user->id) {
            return response()->json(['message' => __('messages.cannot_message_own_listing')], 403);
        }

        if (!$listing->isActive()) {
            return response()->json(['message' => __('messages.listing_not_available')], 422);
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

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor($user);

        return response()->json([
            'conversation' => $conversation->load(['listing', 'buyer', 'seller']),
            'message' => $message->load('sender'),
        ], 201);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('sendMessage', $conversation);

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor($request->user());

        return response()->json($message->load('sender'), 201);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->totalUnreadMessagesCount(),
        ]);
    }
}
