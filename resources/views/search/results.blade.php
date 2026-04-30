@extends('layouts.app')

@section('title', $query ? 'Search: ' . $query : 'Search')

@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-xl font-bold text-charcoal">
            @if($query)
                Results for <span class="text-teal-700">"{{ $query }}"</span>
            @else
                Search
            @endif
        </h1>
        <p class="text-sm text-gray-500 mt-1">Powered by Scolta semantic search — finds meaning, not just keywords</p>
    </div>

    @if(!$query)
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="font-semibold text-charcoal mb-3">Try these searches</h2>
        <div class="flex flex-wrap gap-2">
            @foreach(['puppy problems', 'cooking disasters', 'feeling overwhelmed', 'weekend plans', 'working from home struggles', 'fitness journey', 'pet stories', 'garden updates', 'bad weather complaints'] as $suggestion)
            <a href="{{ route('search', ['q' => $suggestion]) }}" class="bg-teal-50 text-teal-700 hover:bg-teal-100 px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ $suggestion }}
            </a>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-4">These queries demonstrate semantic search: "puppy problems" finds posts about dogs being destructive, not posts containing the words "puppy problems."</p>
    </div>
    @endif

    <x-scolta::search class="scolta-mystream" />
</div>
@endsection

@push('scripts')
<script>
    // Pre-fill the Scolta search box if we have a query from the URL
    @if($query)
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for scolta.js to render, then populate the input
        const tryFill = setInterval(function() {
            const input = document.querySelector('#scolta-search input[type="search"], #scolta-search input[type="text"]');
            if (input) {
                clearInterval(tryFill);
                input.value = {{ json_encode($query) }};
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
            }
        }, 100);
        setTimeout(function() { clearInterval(tryFill); }, 5000);
    });
    @endif
</script>
@endpush
