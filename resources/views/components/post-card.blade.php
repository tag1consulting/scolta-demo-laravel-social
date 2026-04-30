@props(['post', 'compact' => false, 'showThread' => false])

<article class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors">
    <div class="flex gap-3">
        <a href="{{ route('users.show', $post->user) }}" class="flex-shrink-0">
            <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->display_name }}" class="w-10 h-10 rounded-full bg-gray-200">
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-baseline gap-2 flex-wrap">
                <a href="{{ route('users.show', $post->user) }}" class="font-semibold text-charcoal hover:text-teal-700 transition-colors text-sm">
                    {{ $post->user->display_name }}
                </a>
                <a href="{{ route('users.show', $post->user) }}" class="text-gray-400 text-sm hover:text-gray-600">
                    @{{ $post->user->username }}
                </a>
                <span class="text-gray-400 text-xs">·</span>
                <a href="{{ route('posts.show', $post) }}" class="text-gray-400 text-xs hover:text-gray-600" title="{{ $post->created_at->format('M j, Y g:i A') }}">
                    {{ $post->created_at->diffForHumans(['short' => true]) }}
                </a>
            </div>

            @if($post->parent_id && $post->relationLoaded('parent') && $post->parent)
            <p class="text-xs text-gray-400 mt-0.5">
                Replying to
                <a href="{{ route('users.show', $post->parent->user) }}" class="text-teal-600 hover:underline">@{{ $post->parent->user->username ?? '...' }}</a>
            </p>
            @endif

            <a href="{{ route('posts.show', $post) }}" class="block mt-1.5">
                <p class="post-body text-charcoal">{{ $post->body }}</p>
            </a>

            @if($post->hashtags->isNotEmpty())
            <div class="mt-1.5 flex flex-wrap gap-1">
                @foreach($post->hashtags as $tag)
                <a href="{{ route('hashtags.show', $tag) }}" class="hashtag text-sm">#{{ $tag->name }}</a>
                @endforeach
            </div>
            @endif

            <div class="flex items-center gap-5 mt-3">
                <a href="{{ route('posts.show', $post) }}" class="flex items-center gap-1.5 text-gray-400 text-sm hover:text-teal-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    {{ $post->reply_count > 0 ? number_format($post->reply_count) : '' }}
                </a>
                <button class="flex items-center gap-1.5 text-gray-400 text-sm hover:text-teal-600 transition-colors cursor-default" title="Boost (demo only)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ $post->boost_count > 0 ? number_format($post->boost_count) : '' }}
                </button>
                <button class="flex items-center gap-1.5 text-gray-400 text-sm hover:text-coral-500 transition-colors cursor-default" title="Star (demo only)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    {{ $post->star_count > 0 ? number_format($post->star_count) : '' }}
                </button>
            </div>
        </div>
    </div>
</article>
