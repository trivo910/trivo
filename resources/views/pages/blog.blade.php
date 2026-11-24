@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Blog - Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Get the latest blogs on digital marketing trends, SEO, social media, paid advertisement, performance marketing, and others.') : 'Get the latest blogs on digital marketing trends, SEO, social media, paid advertisement, performance marketing, and others.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="blog-page">
        <header class="blog-page__banner">
            <h1>Blog</h1>
        </header>

        <section class="blog-page__content" aria-label="Blog articles">
            <div class="blog-page__cards" id="blog-articles" data-blog-grid>
                @foreach ($posts as $post)
                    <article class="blog-page__card" data-blog-card @if ($loop->iteration > 6) hidden @endif>
                        <div class="blog-page__card-image" aria-hidden="true">
                            @if (($post['featured_media'] ?? null)?->processing_status === 'ready')
                                <x-media.picture
                                    :media="$post['featured_media']"
                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                                    class="blog-page__card-img"
                                />
                            @elseif (! empty($post['image']['src']))
                                <img class="blog-page__card-img" src="{{ $post['image']['src'] }}" alt="" loading="lazy" decoding="async">
                            @endif
                        </div>
                        <div class="blog-page__card-copy">
                            <h2><a href="{{ $post['url'] ?? route('blog.post', ['slug' => $post['slug']]) }}">{{ $post['title'] }}</a></h2>
                            <p>{{ $post['excerpt'] }}</p>
                            <a class="blog-page__read-more" href="{{ $post['url'] ?? route('blog.post', ['slug' => $post['slug']]) }}">Read More <span aria-hidden="true">»</span></a>
                        </div>
                    </article>
                @endforeach
                @if(count($posts) === 0)<p>No articles have been published yet. Please check back soon.</p>@endif
            </div>
            @if (count($posts) > 6)
                <button class="blog-page__load-more" type="button" data-blog-load-more aria-controls="blog-articles">Load More</button>
            @endif
            <div class="pagination">{{ $posts->links() }}</div>
        </section>

        @include('partials.home-clients', ['headingId' => 'blog-clients-title'])
    </article>
@endsection
