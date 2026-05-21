<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('group.' . $this->message->group_id)];
    }

    public function broadcastWith(): array
    {
        $reactions = $this->message->reactionsGrouped()->get()->map(fn($r) => [
            'emoji'    => $r->emoji,
            'count'    => $r->count,
            'user_ids' => explode(',', $r->user_ids),
        ]);

        return [
            'message_id' => $this->message->id,
            'reactions'  => $reactions,
        ];
    }
}