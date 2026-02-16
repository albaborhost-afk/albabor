<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Listing $listing): bool
    {
        // Anyone can view active listings
        if ($listing->status === 'active') {
            return true;
        }

        // Owner can view their own listings
        return $user->id === $listing->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return !$user->isBlocked();
    }

    public function update(User $user, Listing $listing): bool
    {
        if ($user->isBlocked()) {
            return false;
        }

        return $user->id === $listing->user_id || $user->isAdmin();
    }

    public function delete(User $user, Listing $listing): bool
    {
        if ($user->isBlocked()) {
            return false;
        }

        return $user->id === $listing->user_id || $user->isAdmin();
    }
}
