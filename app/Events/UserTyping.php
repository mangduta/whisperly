<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public int $groupId,
        public int $userId,
        public string $userName,
        public bool $isTyping
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('group.' . $this->groupId)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->userId,
            'user_name' => $this->userName,
            'is_typing' => $this->isTyping,
        ];
    }
}