<?php

namespace App\Http\Controllers;

use App\Models\Post;

class FeedController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'hashtags'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate(20);

        return view('feed.index', compact('posts'));
    }
}
