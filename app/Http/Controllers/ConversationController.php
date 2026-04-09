<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\MediationTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::forUser($user)
            ->with(['listing.media', 'buyer', 'seller', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        $mediationTickets = MediationTicket::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['listing.media', 'buyer', 'seller'])
            ->latest('updated_at')
            ->get();

        // Merge both into a single sorted collection
        $allItems = collect();

        foreach ($conversations as $conv) {
            $allItems->push((object) [
                'type' => 'conversation',
                'item' => $conv,
                'sort_date' => $conv->last_message_at ?? $conv->created_at,
            ]);
        }

        foreach ($mediationTickets as $ticket) {
            $allItems->push((object) [
                'type' => 'mediation',
                'item' => $ticket,
                'sort_date' => $ticket->updated_at,
            ]);
        }

        $allItems = $allItems->sortByDesc('sort_date')->values();

        return view('conversations.index', compact('allItems'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        try {
            $this->authorize('view', $conversation);

            $conversation->load(['listing.media', 'buyer', 'seller', 'messages.sender']);
            $conversation->markAsReadFor($request->user());

            $messagesJson = $conversation->messages->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender ? ['name' => $m->sender->name] : null,
                'created_at' => $m->created_at?->toISOString(),
            ])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

            return view('conversations.show', compact('conversation', 'messagesJson'));
        } catch (\Throwable $e) {
            Log::error('Conversation show error', [
                'conversation_id' => $conversation->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

        if ($request->expectsJson()) {
            return response()->json($conversation->messages()->with('sender')->latest()->first(), 201);
        }
        return back()->with('success', __('messages.message_sent'));
    }

    public function messages(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $conversation->markAsReadFor(auth()->user());

        return response()->json(
            $conversation->messages()->with('sender')->orderBy('created_at')->get()
        );
    }
}
