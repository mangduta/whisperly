<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $groups = $user->groups()->with('lastMessage.user')->get();
        return view('groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        /** @var User $user */
        $user  = Auth::user();
        $group = Group::create([...$data, 'created_by' => $user->id]);

        $group->members()->attach($user->id, [
            'role'      => 'admin',
            'joined_at' => now(),
        ]);

        return redirect()->route('groups.show', $group);
    }

    public function show(Group $group)
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($group->members->contains($user->id), 403);

        $messages = $group->messages()
                          ->with(['user', 'reactions', 'replyTo.user', 'reads'])
                          ->oldest()
                          ->get();

        $members = $group->members()->withPivot('role')->get();

        return view('groups.show', compact('group', 'messages', 'members'));
    }

    public function destroy(Group $group)
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($group->created_by === $user->id, 403);
        $group->members()->detach();
        $group->forceDelete();
        return redirect()->route('groups.index');
    }
}