<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class UserStatusChanged implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(public User $user) {}

    public function broadcastOn(): array
    {
        return [new Channel('user-status')];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'        => $this->user->id,
            'status'         => $this->user->status,
            'status_message' => $this->user->status_message,
            'last_seen_at'   => $this->user->last_seen_at?->toISOString(),
        ];
    }
}