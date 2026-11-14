@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Blog - Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Get the latest blogs on digital marketing trends, SEO, social media, paid advertisement, performance marketing, and others.') : 'Get the latest blogs on digital marketing trends, SEO, social media, paid advertisement, performance marketing, and others.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    @php
        $clients = [
            ['name' => 'Al Bushra', 'image' => 'Al-Bushra.png'],
            ['name' => 'Mumbai Darshan', 'image' => 'Mumbai-Darshan.png'],
            ['name' => 'Brady Morris', 'image' => 'Brady-Morris.png'],
            ['name' => 'Stock Sovet', 'image' => 'Stock-Sovet.png'],
            ['name' => 'Sanjari', 'image' => 'Sanjari-2.png'],
            ['name' => 'Polla Tech', 'image' => 'Polla-Tech.png'],
            ['name' => 'Stop Over', 'image' => 'Stop-Over.png'],
            ['name' => 'Quba Dates', 'image' => 'Quba-Dates.png'],
            ['name' => 'Medikash', 'image' => 'Medikash.png'],
            ['name' => 'MYVN', 'image' => 'MYVN.png'],
            ['name' => 'Al-Arafat', 'image' => 'Al-Arafat.png'],
            ['name' => 'Al-Rida', 'image' => 'Al-Rida.png'],
            ['name' => 'Gulshan Tours', 'image' => 'Gulshan-Tours.png'],
            ['name' => 'Global Duniya', 'image' => 'Global-Duniya.png'],
        ];
    @endphp

    <article class="blog-page">
        <header class="blog-page__banner">
            <h1>Blog</h1>
        </header>

        <section class="blog-page__content" aria-label="Blog articles">
            <div class="blog-page__cards" id="blog-articles" data-blog-grid>
                @foreach ($posts as $post)
                    <article class="blog-page__card" data-blog-card @if ($loop->iteration > 6) hidden @endif>
                        <div class="blog-page__card-image" aria-hidden="true"></div>
                        <div class="blog-page__card-copy">
                            <h2><a href="{{ $post['url'] ?? route('blog.post', ['slug' => $post['slug']]) }}">{{ $post['title'] }}</a></h2>
                            <p>{{ $post['excerpt'] }}</p>
                            <a class="blog-page__read-more" href="{{ $post['url'] ?? route('blog.post', ['slug' => $post['slug']]) }}">Read More <span aria-hidden="true">»</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
            @if (count($posts) > 6)
                <button class="blog-page__load-more" type="button" data-blog-load-more aria-controls="blog-articles">Load More</button>
            @endif
        </section>

        <section class="about-page__clients blog-page__clients" aria-labelledby="blog-clients-title">
            <h2 id="blog-clients-title">Clients We work with</h2>
            <ul class="about-page__client-track" aria-label="Client logos">
                @foreach ($clients as $client)
                    <li><img src="{{ asset('wp-content/uploads/2026/03/'.$client['image']) }}" alt="{{ $client['name'] }}" loading="lazy" width="300" height="135"></li>
                @endforeach
            </ul>
        </section>
    </article>
@endsection
