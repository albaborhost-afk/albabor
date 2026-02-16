<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MediationTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediationController extends Controller
{
    /**
     * Liste des tickets de médiation de l'utilisateur (acheteur et vendeur).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $buyerTickets = $user->buyerTickets()
            ->with(['listing.media', 'buyer', 'seller'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sellerTickets = $user->sellerTickets()
            ->with(['listing.media', 'buyer', 'seller'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'buyerTickets' => $buyerTickets,
            'sellerTickets' => $sellerTickets,
        ]);
    }

    /**
     * Créer un nouveau ticket de médiation pour une annonce.
     */
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $user = $request->user();

        // Vérifier que la médiation est activée pour cette annonce
        if (!$listing->mediation_enabled) {
            return response()->json([
                'message' => 'La médiation n\'est pas disponible pour cette annonce.',
            ], 403);
        }

        // Vérifier que l'annonce est active
        if ($listing->status !== 'active') {
            return response()->json([
                'message' => 'Cette annonce n\'est plus disponible.',
            ], 403);
        }

        // Vérifier que l'utilisateur n'est pas le propriétaire
        if ($listing->user_id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas ouvrir une médiation sur votre propre annonce.',
            ], 403);
        }

        // Vérifier qu'il n'y a pas déjà un ticket ouvert
        $existingTicket = MediationTicket::where('listing_id', $listing->id)
            ->where('buyer_id', $user->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->first();

        if ($existingTicket) {
            return response()->json([
                'message' => 'Vous avez déjà un ticket de médiation ouvert pour cette annonce.',
                'ticket' => $existingTicket,
            ], 409);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket = MediationTicket::create([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
            'status' => 'new',
            'messages' => [
                [
                    'user_id' => $user->id,
                    'message' => $validated['message'],
                    'created_at' => now()->toIso8601String(),
                ]
            ],
        ]);

        $ticket->load(['listing.media', 'buyer', 'seller']);

        return response()->json([
            'message' => 'Ticket de médiation créé avec succès.',
            'ticket' => $ticket,
        ], 201);
    }

    /**
     * Afficher un ticket de médiation.
     */
    public function show(Request $request, MediationTicket $ticket): JsonResponse
    {
        $user = $request->user();

        // Seuls l'acheteur, le vendeur ou un admin peuvent voir le ticket
        if ($ticket->buyer_id !== $user->id && $ticket->seller_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à voir ce ticket.',
            ], 403);
        }

        $ticket->load(['listing.media', 'buyer', 'seller']);

        return response()->json([
            'ticket' => $ticket,
        ]);
    }

    /**
     * Ajouter un message à un ticket de médiation.
     */
    public function addMessage(Request $request, MediationTicket $ticket): JsonResponse
    {
        $user = $request->user();

        // Seuls l'acheteur, le vendeur ou un admin peuvent envoyer un message
        if ($ticket->buyer_id !== $user->id && $ticket->seller_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à envoyer un message sur ce ticket.',
            ], 403);
        }

        // Impossible d'ajouter un message à un ticket fermé ou annulé
        if (in_array($ticket->status, ['closed', 'cancelled'])) {
            return response()->json([
                'message' => 'Ce ticket est fermé, vous ne pouvez plus envoyer de messages.',
            ], 403);
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

        return response()->json([
            'message' => 'Message envoyé avec succès.',
            'ticket' => $ticket->fresh(),
        ]);
    }

    /**
     * Annuler un ticket de médiation (uniquement par l'acheteur, statut 'new').
     */
    public function cancel(Request $request, MediationTicket $ticket): JsonResponse
    {
        $user = $request->user();

        // Seul l'acheteur peut annuler
        if ($ticket->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'Seul l\'acheteur peut annuler ce ticket.',
            ], 403);
        }

        // Annulation possible uniquement si le statut est 'new'
        if ($ticket->status !== 'new') {
            return response()->json([
                'message' => 'Ce ticket ne peut plus être annulé.',
            ], 403);
        }

        $ticket->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Ticket de médiation annulé avec succès.',
            'ticket' => $ticket->fresh(),
        ]);
    }
}
