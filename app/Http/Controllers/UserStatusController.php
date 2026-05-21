<?php
namespace App\Http\Controllers;

use App\Events\UserStatusChanged;
use Illuminate\Http\Request;

class UserStatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'status'         => 'required|in:online,offline,away',
            'status_message' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $user->update([
            'status'         => $request->status,
            'status_message' => $request->status_message,
            'last_seen_at'   => now(),
        ]);

        broadcast(new UserStatusChanged($user));

        return response()->json(['success' => true]);
    }
}