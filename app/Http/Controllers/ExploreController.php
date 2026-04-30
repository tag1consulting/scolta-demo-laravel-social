<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;

class ExploreController extends Controller
{
    public function index()
    {
        $trendingHashtags = Hashtag::orderByDesc('post_count')->take(15)->get();

        $popularPosts = Post::with(['user', 'hashtags'])
            ->whereNull('parent_id')
            ->orderByDesc('star_count')
            ->take(20)
            ->get();

        $suggestedUsers = User::withCount('posts')
            ->orderByDesc('posts_count')
            ->take(8)
            ->get();

        return view('explore.index', compact('trendingHashtags', 'popularPosts', 'suggestedUsers'));
    }
}
