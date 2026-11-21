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
            <div class="blog-post__meta">
                @if(!empty($article['author']))<span>{{ $article['author'] }}</span>@endif
                @if(!empty($article['category']))<span>{{ $article['category'] }}</span>@endif
                @if(!empty($article['date']))<span>{{ $article['date'] }}</span>@endif
                @if(!empty($article['reading_time']))<span>{{ $article['reading_time'] }} min read</span>@endif
            </div>
        </header>

        @if($featuredMedia || !empty($featuredImage['src']))<figure class="blog-post__hero">
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
        </figure>@endif

        @if(!empty($toc))
            <nav class="blog-post__toc" aria-label="Table of contents"><h2>In this article</h2><ol>
                @foreach($toc as $item)<li><a href="#{{ $item['id'] }}">{{ $item['title'] }}</a></li>@endforeach
            </ol></nav>
        @endif
        <div class="blog-post__body">
            {!! $bodyHtml !!}
        </div>
        @if(!empty($article['tags']))<div class="blog-post__tags" aria-label="Article tags">@foreach($article['tags'] as $tag)<span>{{ $tag }}</span>@endforeach</div>@endif
    </article>
@endsection
