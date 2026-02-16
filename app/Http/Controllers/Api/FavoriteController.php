<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favoritedListings()
            ->with(['media', 'user'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(20);

        return response()->json($favorites);
    }

    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        $user = $request->user();

        if ($user->hasFavorited($listing)) {
            $user->favorites()->where('listing_id', $listing->id)->delete();
            $listing->decrement('favorites_count');
            $favorited = false;
        } else {
            $user->favorites()->create(['listing_id' => $listing->id]);
            $listing->increment('favorites_count');
            $favorited = true;
        }

        return response()->json([
            'success' => true,
            'favorited' => $favorited,
            'message' => $favorited ? 'Ajouté aux favoris.' : 'Retiré des favoris.',
            'count' => $listing->fresh()->favorites_count,
        ]);
    }
}
