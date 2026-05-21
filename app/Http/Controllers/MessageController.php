<?php
namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Group;
use App\Models\Message;
use App\Models\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request, Group $group)
    {
        abort_unless($group->members()->where('user_id', auth()->id())->exists(), 403);

        $request->validate([
            'body'       => 'nullable|string|max:5000|required_without:file',
            'file'       => 'nullable|file|max:10240',
            'reply_to_id'=> 'nullable|exists:messages,id',
        ]);

        $filePath = $fileName = $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store('chat-files', 'public');
            $fileName = $file->getClientOriginalName();
            $fileType = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';
        }

        $message = Message::create([
            'group_id'    => $group->id,
            'user_id'     => auth()->id(),
            'body'        => $request->body,
            'reply_to_id' => $request->reply_to_id,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_type'   => $fileType,
        ]);

        $message->load('user', 'replyTo.user');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true]);
    }

    public function destroy(Message $message)
    {
        abort_unless($message->user_id === auth()->id(), 403);
        $message->delete();
        return response()->json(['success' => true]);
    }

    public function markRead(Message $message)
    {
        MessageRead::firstOrCreate([
            'message_id' => $message->id,
            'user_id'    => auth()->id(),
        ], ['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function typing(Request $request, Group $group)
    {
        broadcast(new UserTyping(
            $group->id,
            auth()->id(),
            auth()->user()->name,
            $request->boolean('is_typing')
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}