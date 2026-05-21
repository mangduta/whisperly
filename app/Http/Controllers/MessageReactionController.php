<?php
namespace App\Http\Controllers;

use App\Events\MessageReactionUpdated;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\Request;

class MessageReactionController extends Controller
{
    public function toggle(Request $request, Message $message)
    {
        $request->validate(['emoji' => 'required|string|max:10']);

        $existing = MessageReaction::where([
            'message_id' => $message->id,
            'user_id'    => auth()->id(),
            'emoji'      => $request->emoji,
        ])->first();

        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => auth()->id(),
                'emoji'      => $request->emoji,
            ]);
        }

        broadcast(new MessageReactionUpdated($message->fresh()))->toOthers();

        $reactions = $message->fresh()->reactionsGrouped()->get()->map(fn($r) => [
            'emoji'    => $r->emoji,
            'count'    => $r->count,
            'user_ids' => explode(',', $r->user_ids),
        ]);

        return response()->json(['reactions' => $reactions]);
    }
}