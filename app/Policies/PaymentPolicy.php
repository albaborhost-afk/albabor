<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return !$user->isBlocked();
    }

    public function update(User $user, Payment $payment): bool
    {
        // Only admin can update payments (approve/reject)
        return $user->isAdmin();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
