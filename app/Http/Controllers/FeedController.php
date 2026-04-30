<?php

namespace App\Http\Controllers;

use App\Models\Post;

class FeedController extends Controller
{
    public function index()
    {
        $seed = date('Ymd');
        $posts = Post::with(['user', 'hashtags'])
            ->whereNull('parent_id')
            ->orderByRaw("RAND($seed)")
            ->paginate(20);

        return view('feed.index', compact('posts'));
    }
}
