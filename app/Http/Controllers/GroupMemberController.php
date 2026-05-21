<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    public function store(Request $request, Group $group)
    {
        abort_unless($group->admins->contains(auth()->id()), 403);

        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::firstWhere('email', $request->email);

        if ($group->members->contains($user->id)) {
            return back()->with('error', 'Pengguna sudah ada di grup.');
        }

        $group->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(Group $group, User $user)
    {
        abort_unless($group->admins->contains(auth()->id()), 403);
        $group->members()->detach($user->id);
        return back()->with('success', 'Anggota berhasil dihapus.');
    }

    public function promote(Group $group, User $user)
    {
        abort_unless($group->created_by === auth()->id(), 403);
        $group->members()->updateExistingPivot($user->id, ['role' => 'admin']);
        return back()->with('success', 'Anggota dijadikan admin.');
    }
}