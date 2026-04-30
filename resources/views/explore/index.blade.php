@extends('layouts.app')

@section('title', 'Explore')

@section('content')
<div class="space-y-6">
    <h1 class="text-xl font-bold text-charcoal">Explore</h1>

    @if($trendingHashtags->isNotEmpty())
    <section>
        <h2 class="text-base font-semibold text-charcoal mb-3">Trending topics</h2>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            @foreach($trendingHashtags as $tag)
            <a href="{{ route('hashtags.show', $tag) }}" class="flex justify-between items-center px-4 py-3 hover:bg-teal-50 transition-colors border-b border-gray-100 last:border-0">
                <span class="text-teal-700 font-semibold">#{{ $tag->name }}</span>
                <span class="text-gray-400 text-sm">{{ number_format($tag->post_count) }} posts</span>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    @if($suggestedUsers->isNotEmpty())
    <section>
        <h2 class="text-base font-semibold text-charcoal mb-3">People to follow</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($suggestedUsers as $u)
            <a href="{{ route('users.show', $u) }}" class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-teal-300 transition-colors">
                <img src="{{ $u->avatar_url }}" alt="" class="w-12 h-12 rounded-full flex-shrink-0 bg-gray-200">
                <div class="min-w-0">
                    <p class="font-semibold text-charcoal truncate">{{ $u->display_name }}</p>
                    <p class="text-gray-400 text-sm truncate">@{{ $u->username }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">{{ number_format($u->posts_count) }} posts</p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    @if($popularPosts->isNotEmpty())
    <section>
        <h2 class="text-base font-semibold text-charcoal mb-3">Popular posts</h2>
        <div class="space-y-3">
            @foreach($popularPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
