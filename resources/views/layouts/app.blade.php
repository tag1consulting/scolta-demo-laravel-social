<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MyStream') — MyStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            900: '#134e4a',
                            800: '#115e59',
                            700: '#0f766e',
                            600: '#0d9488',
                            100: '#ccfbf1',
                            50:  '#f0fdfa',
                        },
                        coral: {
                            500: '#e07a5f',
                            400: '#e8967f',
                            100: '#fde8e2',
                        },
                        warm: {
                            50:  '#F8F8F6',
                            100: '#f0f0ee',
                        },
                        charcoal: '#374151',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/vendor/scolta/scolta.css">
    <style>
        body { background-color: #F8F8F6; color: #374151; font-family: 'Inter', system-ui, sans-serif; }
        .post-body { line-height: 1.6; font-size: 1rem; }
        .hashtag { color: #0f766e; font-weight: 500; }
        .hashtag:hover { text-decoration: underline; }
    </style>
</head>
<body class="min-h-screen">

    {{-- Top bar --}}
    <header class="bg-teal-800 text-white sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-4">
            <a href="{{ route('feed') }}" class="text-xl font-bold tracking-tight flex-shrink-0 hover:text-teal-100 transition-colors">
                MyStream
            </a>
            <div class="flex-1 max-w-lg">
                <form action="{{ route('search') }}" method="GET" class="relative">
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search posts..."
                        class="w-full bg-teal-700 text-white placeholder-teal-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300 focus:bg-teal-600"
                        autocomplete="off"
                    >
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-200 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('explore') }}" class="text-sm text-teal-100 hover:text-white transition-colors hidden sm:block">Explore</a>
                <button class="text-sm bg-coral-500 hover:bg-coral-400 px-4 py-2 rounded-full font-medium transition-colors opacity-75 cursor-not-allowed" title="Login disabled in demo">Log in</button>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex gap-6">

            {{-- Left sidebar (desktop) --}}
            <aside class="hidden lg:block w-52 flex-shrink-0">
                <nav class="sticky top-20 space-y-1">
                    <a href="{{ route('feed') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-teal-50 hover:text-teal-800 font-medium transition-colors {{ request()->routeIs('feed') ? 'bg-teal-50 text-teal-800' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Home
                    </a>
                    <a href="{{ route('explore') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-teal-50 hover:text-teal-800 font-medium transition-colors {{ request()->routeIs('explore') ? 'bg-teal-50 text-teal-800' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        Explore
                    </a>
                    <a href="{{ route('search') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-charcoal hover:bg-teal-50 hover:text-teal-800 font-medium transition-colors {{ request()->routeIs('search') ? 'bg-teal-50 text-teal-800' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search
                    </a>
                    <div class="pt-4 border-t border-gray-200 mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-2">MyStream</p>
                        <p class="text-xs text-gray-500 px-3 leading-relaxed">A demo platform showcasing Scolta semantic search on social content.</p>
                    </div>
                </nav>
            </aside>

            {{-- Main content --}}
            <main class="flex-1 min-w-0 max-w-2xl">
                @yield('content')
            </main>

            {{-- Right sidebar (desktop) --}}
            <aside class="hidden xl:block w-64 flex-shrink-0">
                <div class="sticky top-20 space-y-4">

                    {{-- Search callout --}}
                    <div class="bg-teal-800 text-white rounded-xl p-4">
                        <h3 class="font-semibold text-sm mb-1">Semantic Search</h3>
                        <p class="text-xs text-teal-100 leading-relaxed">Search understands meaning, not just keywords. Try <a href="{{ route('search', ['q' => 'puppy problems']) }}" class="underline hover:text-white">puppy problems</a> or <a href="{{ route('search', ['q' => 'cooking disasters']) }}" class="underline hover:text-white">cooking disasters</a>.</p>
                    </div>

                    {{-- Trending hashtags --}}
                    @php
                        $sidebarHashtags = \App\Models\Hashtag::orderByDesc('post_count')->take(8)->get();
                    @endphp
                    @if($sidebarHashtags->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-sm text-charcoal mb-3">Trending topics</h3>
                        <div class="space-y-2">
                            @foreach($sidebarHashtags as $tag)
                            <a href="{{ route('hashtags.show', $tag) }}" class="flex justify-between items-center text-sm hover:bg-teal-50 -mx-2 px-2 py-1 rounded-lg transition-colors">
                                <span class="text-teal-700 font-medium">#{{ $tag->name }}</span>
                                <span class="text-gray-400 text-xs">{{ number_format($tag->post_count) }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Suggested users --}}
                    @php
                        $sidebarUsers = \App\Models\User::withCount('posts')->orderByDesc('posts_count')->take(5)->get();
                    @endphp
                    @if($sidebarUsers->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-sm text-charcoal mb-3">Who to follow</h3>
                        <div class="space-y-3">
                            @foreach($sidebarUsers as $u)
                            <a href="{{ route('users.show', $u) }}" class="flex items-center gap-2.5 hover:bg-teal-50 -mx-2 px-2 py-1 rounded-lg transition-colors">
                                <img src="{{ $u->avatar_url }}" alt="" class="w-8 h-8 rounded-full flex-shrink-0 bg-gray-200">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-charcoal truncate">{{ $u->display_name }}</p>
                                    <p class="text-xs text-gray-400 truncate">@{{ $u->username }}</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </aside>

        </div>
    </div>

    {{-- Mobile bottom nav --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-2 z-50">
        <a href="{{ route('feed') }}" class="flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('feed') ? 'text-teal-700' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs">Home</span>
        </a>
        <a href="{{ route('explore') }}" class="flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('explore') ? 'text-teal-700' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
            <span class="text-xs">Explore</span>
        </a>
        <a href="{{ route('search') }}" class="flex flex-col items-center gap-0.5 px-4 py-1 {{ request()->routeIs('search') ? 'text-teal-700' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-xs">Search</span>
        </a>
    </nav>

    <script src="/vendor/scolta/wasm/scolta_core.js"></script>
    <script src="/vendor/scolta/scolta.js"></script>
    @stack('scripts')
</body>
</html>
