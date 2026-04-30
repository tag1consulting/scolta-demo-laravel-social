@extends('layouts.app')

@section('title', '#' . $hashtag->name)

@section('content')
<div class="space-y-3">
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <h1 class="text-2xl font-bold text-teal-700">#{{ $hashtag->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ number_format($hashtag->post_count) }} posts</p>
    </div>

    @forelse($posts as $post)
        <x-post-card :post="$post" />
    @empty
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <p class="text-gray-500">No posts with this hashtag yet.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
    <div class="pt-2">
        {{ $posts->links() }}
    </div>
    @endif
</div>
@endsection
