<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Auth::user()
            ->favoritedListings()
            ->with(['media', 'user'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(20);

        return view('favorites.index', compact('favorites'));
    }

    public function toggle(Listing $listing)
    {
        $user = Auth::user();

        if ($user->hasFavorited($listing)) {
            $user->favorites()->where('listing_id', $listing->id)->delete();
            $listing->decrement('favorites_count');
            $message = __('messages.favorite_removed');
            $favorited = false;
        } else {
            $user->favorites()->create(['listing_id' => $listing->id]);
            $listing->increment('favorites_count');
            $message = __('messages.favorite_added');
            $favorited = true;
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'favorited' => $favorited,
                'message' => $message,
                'count' => $listing->fresh()->favorites_count,
            ]);
        }

        return back()->with('success', $message);
    }
}
