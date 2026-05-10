@extends('layouts.app')

@section('title', 'About This Demo')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
    <h1 class="text-2xl font-bold text-charcoal">About This Demo</h1>

    <div>
        <h2 class="text-lg font-semibold text-teal-800 mb-2">About This Site</h2>
        <p class="text-charcoal leading-relaxed">MyStream is a fictional social media platform. It was created by Tag1 Consulting to demonstrate the capabilities of Scolta, an open-source AI-powered search platform, on a Laravel-based social networking application with user-generated content.</p>
    </div>

    <div>
        <h2 class="text-lg font-semibold text-teal-800 mb-2">What You Are Looking At</h2>
        <p class="text-charcoal leading-relaxed mb-3">This site is a Laravel demonstration built to show how Scolta performs on short-form, user-generated content typical of social media platforms. The site contains thousands of posts, hashtags, and user profiles covering a wide range of topics including technology, science, culture, and current events.</p>
        <p class="text-charcoal leading-relaxed">The content illustrates how Scolta can handle the informal, hashtag-heavy, short-form writing style of social platforms — a challenging indexing scenario that differs significantly from long-form editorial or technical content.</p>
    </div>

    <div>
        <h2 class="text-lg font-semibold text-teal-800 mb-2">What Scolta Does Here</h2>
        <p class="text-charcoal leading-relaxed mb-3">The search bar uses Scolta to let you explore posts and discussions using natural language. Try these example queries:</p>
        <ul class="list-disc list-inside space-y-1 text-charcoal mb-3 pl-2">
            <li>"posts about renewable energy"</li>
            <li>"discussions about artificial intelligence"</li>
            <li>"what are people saying about climate change?"</li>
            <li>"technology news"</li>
        </ul>
        <p class="text-charcoal leading-relaxed">Scolta uses Pagefind for full-text indexing, Claude via the Anthropic API for query expansion and AI-generated overviews, and a custom BM25-based scoring layer adapted for short-form content.</p>
    </div>

    <div>
        <h2 class="text-lg font-semibold text-teal-800 mb-2">About Tag1 Consulting</h2>
        <p class="text-charcoal leading-relaxed">Tag1 Consulting is one of the leading Drupal development and consulting firms in the world. Tag1 built and open-sources Scolta as a demonstration of what AI-augmented content discovery can look like on modern web platforms — including Laravel social applications. For more information about Tag1 and Scolta, visit <a href="https://tag1.com" class="text-teal-700 hover:underline">tag1.com</a>.</p>
    </div>

    <div>
        <h2 class="text-lg font-semibold text-teal-800 mb-2">Reuse and Attribution</h2>
        <p class="text-charcoal leading-relaxed">If you are evaluating Scolta for your organization and have questions about how this demo was built or how to implement Scolta for your use case, contact Tag1 Consulting.</p>
    </div>
</div>
@endsection
