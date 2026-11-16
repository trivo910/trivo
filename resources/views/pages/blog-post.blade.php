@extends('layouts.public')

@section('title', $article['seo_title'] ?: $article['title'].' | Trivo Blog')
@section('description', $article['seo_description'] ?: \Illuminate\Support\Str::limit(strip_tags($article['content']), 180))
@section('canonical', $article['canonical_url'] ?: request()->url())
@section('robots', $article['robots'] ?: config('seo.default_robots'))
@section('marketingLayout', 'true')

@section('content')
    <article class="blog-page blog-post-page">
        <header class="blog-post__header">
            <h1>{{ $article['heading'] ?? $article['title'] }}</h1>
        </header>

        <figure class="blog-post__hero">
            @if ($featuredMedia)
                <x-media.picture
                    :media="$featuredMedia"
                    :priority="true"
                    sizes="(max-width: 760px) calc(100vw - 40px), 1220px"
                    class="blog-post__hero-image"
                />
            @elseif (! empty($featuredImage['src']))
                <img
                    class="blog-post__hero-image"
                    src="{{ $featuredImage['src'] }}"
                    alt="{{ $featuredImage['alt'] }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            @else
                <div class="blog-post__hero-placeholder" aria-hidden="true"></div>
            @endif
        </figure>

        <div class="blog-post__body">
            {!! $bodyHtml !!}
        </div>
    </article>
@endsection
