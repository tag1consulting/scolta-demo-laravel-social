@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="space-y-3">
    <h1 class="text-xl font-bold text-charcoal">Home</h1>

    @forelse($posts as $post)
        <x-post-card :post="$post" />
    @empty
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <p class="text-gray-500">No posts yet. Content will appear here after generation.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
    <div class="pt-2">
        {{ $posts->links() }}
    </div>
    @endif
</div>
@endsection
