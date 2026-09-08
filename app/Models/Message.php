<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
    ];

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            $conversation = $message->conversation;
            $recipient = $message->sender_id === $conversation->buyer_id
                ? $conversation->seller : $conversation->buyer;

            if ($recipient && $recipient->email && ! $recipient->isBlocked()) {
                try {
                    $recipient->notify(new \App\Notifications\NewConversationMessage($conversation->id));
                } catch (\Throwable $error) {
                    // A temporary mail/queue outage must not lose an already saved message.
                    \Log::warning('Message notification could not be queued', [
                        'message_id' => $message->id, 'error' => $error->getMessage(),
                    ]);
                }
            }
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
