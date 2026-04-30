<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;

class HashtagController extends Controller
{
    public function show(Hashtag $hashtag)
    {
        $posts = $hashtag->posts()
            ->with(['user', 'hashtags'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate(20);

        return view('hashtags.show', compact('hashtag', 'posts'));
    }
}
