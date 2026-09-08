<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConversationMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $conversationId)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // No sender identity, address or message preview: private profiles stay private in email too.
        return (new MailMessage)
            ->subject('Nouveau message sur AlBabor')
            ->greeting('Bonjour !')
            ->line('Vous avez reçu un nouveau message dans votre messagerie AlBabor.')
            ->action('Lire et répondre', route('conversations.show', $this->conversationId))
            ->line('Connectez-vous à votre compte pour consulter la conversation.');
    }
}
