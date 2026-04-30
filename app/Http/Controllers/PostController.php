<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function show(Post $post)
    {
        $post->load(['user', 'hashtags', 'parent.user', 'replies.user', 'replies.hashtags']);

        return view('posts.show', compact('post'));
    }
}
