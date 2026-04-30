@extends('layouts.app')

@section('title', $user->display_name)

@section('content')
<div class="space-y-4">
    {{-- Profile header --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="h-24 bg-gradient-to-r from-teal-700 to-teal-500"></div>
        <div class="px-4 pb-4">
            <div class="flex items-end justify-between -mt-8 mb-3">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->display_name }}" class="w-16 h-16 rounded-full border-4 border-white bg-gray-200">
                <button class="text-sm border border-gray-300 text-charcoal px-4 py-1.5 rounded-full font-medium hover:bg-gray-50 transition-colors opacity-75 cursor-not-allowed" title="Follow disabled in demo">Follow</button>
            </div>
            <h1 class="text-lg font-bold text-charcoal">{{ $user->display_name }}</h1>
            <p class="text-gray-400 text-sm">{{ '@'.$user->username }}</p>
            @if($user->bio)
            <p class="text-charcoal text-sm mt-2 leading-relaxed">{{ $user->bio }}</p>
            @endif
            <div class="flex items-center gap-4 mt-3 text-sm">
                <span class="text-charcoal"><strong>{{ number_format($postCount) }}</strong> <span class="text-gray-400">posts</span></span>
                <span class="text-gray-400 text-xs">Joined {{ $user->joined_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Posts --}}
    @forelse($posts as $post)
        <x-post-card :post="$post" />
    @empty
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <p class="text-gray-500">No posts yet.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
    <div class="pt-2">
        {{ $posts->links() }}
    </div>
    @endif
</div>
@endsection
