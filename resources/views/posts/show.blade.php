@extends('layouts.app')

@section('title', $post->user->display_name . ': ' . Str::limit($post->body, 60))

@section('content')
<div class="space-y-3">
    <div class="flex items-center gap-2 mb-1">
        <a href="{{ url()->previous(route('feed')) }}" class="text-gray-400 hover:text-charcoal transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-semibold text-charcoal">Post</h1>
    </div>

    {{-- Parent post if this is a reply --}}
    @if($post->parent)
    <div class="opacity-75">
        <x-post-card :post="$post->parent" />
    </div>
    <div class="flex items-center gap-2 text-xs text-gray-400 px-2">
        <div class="w-8 flex justify-center">
            <div class="w-0.5 h-4 bg-gray-300"></div>
        </div>
        <span>Replying to @{{ $post->parent->user->username }}</span>
    </div>
    @endif

    {{-- The post itself --}}
    <div class="bg-white border border-teal-200 rounded-xl p-4">
        <div class="flex gap-3">
            <a href="{{ route('users.show', $post->user) }}" class="flex-shrink-0">
                <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->display_name }}" class="w-12 h-12 rounded-full bg-gray-200">
            </a>
            <div class="flex-1 min-w-0">
                <a href="{{ route('users.show', $post->user) }}" class="font-semibold text-charcoal hover:text-teal-700 transition-colors">
                    {{ $post->user->display_name }}
                </a>
                <a href="{{ route('users.show', $post->user) }}" class="block text-gray-400 text-sm hover:text-gray-600">
                    @{{ $post->user->username }}
                </a>
            </div>
        </div>
        <p class="post-body text-charcoal text-lg mt-3 leading-relaxed">{{ $post->body }}</p>
        @if($post->hashtags->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-1">
            @foreach($post->hashtags as $tag)
            <a href="{{ route('hashtags.show', $tag) }}" class="hashtag">#{{ $tag->name }}</a>
            @endforeach
        </div>
        @endif
        <p class="text-gray-400 text-sm mt-3">{{ $post->created_at->format('g:i A · M j, Y') }}</p>
        <div class="flex items-center gap-6 pt-3 mt-3 border-t border-gray-100 text-sm text-gray-500">
            <span><strong class="text-charcoal">{{ number_format($post->reply_count) }}</strong> replies</span>
            <span><strong class="text-charcoal">{{ number_format($post->boost_count) }}</strong> boosts</span>
            <span><strong class="text-charcoal">{{ number_format($post->star_count) }}</strong> stars</span>
        </div>
    </div>

    {{-- Replies --}}
    @if($post->replies->isNotEmpty())
    <div class="space-y-3 pl-6 border-l-2 border-gray-200 ml-5">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider pt-1">Replies</h2>
        @foreach($post->replies as $reply)
            <x-post-card :post="$reply" />
        @endforeach
    </div>
    @endif
</div>
@endsection
