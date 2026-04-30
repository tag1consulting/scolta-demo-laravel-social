@extends('layouts.app')

@section('title', $query ? 'Search: ' . $query : 'Search')

@section('content')
<style>
    /* Theme Scolta to match MyStream's teal/coral palette */
    #scolta-search {
        --scolta-primary: #115e59;
        --scolta-primary-hover: #0f766e;
        --scolta-accent: #0d9488;
        --scolta-bg: #f0fdfa;
        --scolta-card-bg: #ffffff;
        --scolta-text: #374151;
        --scolta-text-muted: #6b7280;
        --scolta-border: #e5e7eb;
        --scolta-highlight: #ccfbf1;
        --scolta-summary-bg-start: #f0fdfa;
        --scolta-summary-bg-mid: #e6fffa;
        --scolta-summary-border: #99f6e4;
        --scolta-badge-bg: #ccfbf1;
    }
    #scolta-search .scolta-search-box input[type="text"] {
        border-radius: 9999px;
        padding-left: 1.25rem;
        font-size: 1rem;
        border-color: #d1fae5;
        background: #f0fdfa;
    }
    #scolta-search .scolta-search-btn {
        border-radius: 9999px;
        padding: 0.65rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
    }
    #scolta-search .scolta-result-card {
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        transition: border-color 0.15s;
    }
    #scolta-search .scolta-result-card:hover {
        border-color: #99f6e4;
    }
    #scolta-search .scolta-result-title a {
        color: #115e59;
        font-weight: 600;
    }
    #scolta-search .scolta-expand-term {
        background: #ccfbf1;
        color: #0f766e;
        border: 1px solid #99f6e4;
    }
    #scolta-search .scolta-ai-summary {
        border-radius: 0.75rem;
    }
    #scolta-search .scolta-badge {
        background: #ccfbf1;
        color: #0f766e;
    }
</style>

<div class="space-y-4">
    <div>
        <h1 class="text-xl font-bold text-charcoal">
            @if($query)
                Results for <span class="text-teal-700">"{{ $query }}"</span>
            @else
                Search
            @endif
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Powered by Scolta semantic search — finds meaning, not just keywords</p>
    </div>

    @if(!$query)
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="font-semibold text-charcoal mb-3">Try these searches</h2>
        <div class="flex flex-wrap gap-2">
            @foreach(['puppy problems', 'cooking disasters', 'feeling overwhelmed', 'weekend plans', 'working from home struggles', 'fitness journey', 'pet stories', 'garden updates', 'bad weather complaints'] as $suggestion)
            <a href="{{ route('search', ['q' => $suggestion]) }}" class="bg-teal-50 text-teal-700 hover:bg-teal-100 px-3 py-1.5 rounded-full text-sm font-medium transition-colors border border-teal-100">
                {{ $suggestion }}
            </a>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-4 leading-relaxed">These demonstrate semantic search: "puppy problems" returns posts about dogs being chaotic — not posts containing those exact words.</p>
    </div>
    @endif

    <x-scolta::search />
</div>
@endsection

@push('scripts')
<script>
    @if($query)
    document.addEventListener('DOMContentLoaded', function() {
        const tryFill = setInterval(function() {
            const input = document.querySelector('#scolta-search input[type="text"]');
            if (input) {
                clearInterval(tryFill);
                const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeInputValueSetter.call(input, {{ json_encode($query) }});
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }, 150);
        setTimeout(() => clearInterval(tryFill), 8000);
    });
    @endif
</script>
@endpush
