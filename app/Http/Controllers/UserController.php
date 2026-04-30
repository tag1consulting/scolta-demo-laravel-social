<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        $posts = $user->posts()
            ->with(['hashtags'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate(20);

        $postCount = $user->posts()->count();

        return view('users.profile', compact('user', 'posts', 'postCount'));
    }
}
