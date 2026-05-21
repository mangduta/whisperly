<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('group.' . $this->message->group_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'        => $this->message->id,
                'body'      => $this->message->body,
                'file_path' => $this->message->file_path,
                'file_name' => $this->message->file_name,
                'file_type' => $this->message->file_type,
                'created_at'=> $this->message->created_at->toISOString(),
                'user'      => [
                    'id'     => $this->message->user->id,
                    'name'   => $this->message->user->name,
                    'avatar' => $this->message->user->profile_photo_url ?? null,
                ],
                'reply_to'  => $this->message->replyTo ? [
                    'id'   => $this->message->replyTo->id,
                    'body' => $this->message->replyTo->body,
                    'user' => ['name' => $this->message->replyTo->user->name],
                ] : null,
            ],
        ];
    }
}