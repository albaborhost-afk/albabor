<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->isParticipant($user) || $user->isAdmin();
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $conversation->isParticipant($user) && !$user->isBlocked();
    }
}
