<?php
use App\Models\Group;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return Group::find($groupId)?->members->contains($user->id);
});

Broadcast::channel('user-status', function ($user) {
    return $user !== null;
});