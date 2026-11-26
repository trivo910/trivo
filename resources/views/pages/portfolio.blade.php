@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Portfolio | Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Delivering real results') : 'Explore Trivo’s digital marketing, web design, and travel growth portfolio.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    @php
        $projects = app(\App\Services\Content\PortfolioCards::class)->published();

        if ($projects === []) {
            $projects = [
                ['title' => 'Luhaif Perfumes', 'date' => 'May 2026', 'datetime' => '2026-05', 'path' => '/luhaif-perfumes', 'image' => 'wp-content/uploads/2026/05/Luhaif-Thumbnail.png'],
                ['title' => 'Mumbai Darshan', 'date' => 'May 2026', 'datetime' => '2026-05', 'path' => '/mumbai-darshan', 'image' => 'wp-content/uploads/2026/05/Mumbai-Thumbnail.png'],
                ['title' => 'Sell Sold', 'date' => 'May 2026', 'datetime' => '2026-05', 'path' => '/sell-sold', 'image' => 'wp-content/uploads/2026/05/Sell-Sold-Thumbnail.png'],
                ['title' => 'Professional Frameworks', 'date' => 'May 2026', 'datetime' => '2026-05', 'path' => '/professional-frameworks', 'image' => 'wp-content/uploads/2026/05/Professional-Frameworks-Thumbnail.png'],
                ['title' => 'Arihant Panel Fittings', 'date' => 'May 2026', 'datetime' => '2026-05', 'path' => '/arihant-panel-fittings', 'image' => 'wp-content/uploads/2026/05/Arihant-Thumbnail.png'],
                ['title' => 'Al Bushra T & T', 'date' => 'March 2026', 'datetime' => '2026-03', 'path' => '/al-bushra-t-and-t', 'image' => 'wp-content/uploads/2026/03/Al-Bushra-Thumbnail.png'],
                ['title' => 'Stock Sovet Conversion', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/stocksovet', 'image' => 'wp-content/uploads/2026/03/Stock-Sovet-Thumbnail.png'],
                ['title' => 'Rashid Miftahi', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/rashidmiftahi', 'image' => 'wp-content/uploads/2026/03/Rashid-Miftahi-Thumbnail.png'],
                ['title' => 'Areeba Tourism', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/areebatourism', 'image' => 'wp-content/uploads/2026/03/Areeba-Thumbnail.png'],
                ['title' => 'Sanjari Recycling', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/sanjari-recycling', 'image' => 'wp-content/uploads/2026/03/Sanjari-Thumbnail.png'],
                ['title' => 'Holiday Aapke Liye', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/holidayaapkliye', 'image' => 'wp-content/uploads/2026/05/Holiday-Aapke-Liye-Thumbnail.png'],
                ['title' => 'Villa Planet', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/villaplanet', 'image' => 'wp-content/uploads/2026/05/Villa-Planet-Thumbnail.png'],
                ['title' => 'Brady Morris', 'date' => 'May 2023', 'datetime' => '2023-05', 'path' => '/bradymorris', 'image' => 'wp-content/uploads/2026/05/Brady-Morris-Thumbnail.png'],
                ['title' => 'Miftahi Shop', 'date' => 'June 2022', 'datetime' => '2022-06', 'path' => '/miftahi-shop', 'image' => 'wp-content/uploads/2026/05/Miftahi-Shop-Thumbnail.png'],
            ];
        }
    @endphp

    <article class="portfolio-page">
        <header class="portfolio-page__banner">
            <h1>Portfolio</h1>
        </header>

        <section class="portfolio-page__intro" aria-labelledby="portfolio-intro-title">
            <h2 id="portfolio-intro-title">Elevate Your Business with our Diverse Digital Marketing and IT Portfolio</h2>
            <p>If you’re looking for dynamic, affordable, and comprehensive digital marketing services for your business, Trivo IT Solutions creates a customized portfolio designed around your goals. SEO, PPC, web design, social media, email marketing, content creation, and video editing are offered by our experienced team.</p>
        </section>

        <section class="portfolio-page__projects" aria-label="Portfolio projects">
            <div class="portfolio-page__grid">
                @foreach ($projects as $project)
                    <article class="portfolio-project">
                        <a class="portfolio-project__link" href="{{ url($project['path']) }}">
                            <img
                                src="{{ asset($project['image']) }}"
                                alt="{{ $project['title'] }} portfolio project"
                                width="770"
                                height="550"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                @if ($loop->first) fetchpriority="high" @endif
                            >
                            <div class="portfolio-project__details">
                                <time datetime="{{ $project['datetime'] }}">{{ $project['date'] }}</time>
                                <h3>{{ $project['title'] }}</h3>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="portfolio-page__reviews" aria-labelledby="portfolio-reviews-title">
            <h2 id="portfolio-reviews-title">What Our Happy Customers Are Saying</h2>
            <div class="portfolio-page__review-card">
                <div class="portfolio-page__testimonial-carousel home-results__testimonials" aria-label="Customer testimonials">
                    <figure class="portfolio-page__testimonial home-results__testimonial">
                        <blockquote>
                            <p>“Trivo exceeded my expectations! Their professional, creative, and responsive team crafted a tailored marketing strategy that significantly boosted our engagement and sales. With clear communication and valuable insights, they made the entire process seamless.”</p>
                        </blockquote>
                        <figcaption><strong>Ahfaz Panwar</strong><span>Director</span></figcaption>
                    </figure>
                    <figure class="portfolio-page__testimonial home-results__testimonial" hidden>
                        <blockquote>
                            <p>“We are happy with Trivo IT Solution. They are doing a great job for us far beyond our expectations. We contracted them with a goal to become an online supplier for travel agencies and they fulfilled it very nicely.”</p>
                        </blockquote>
                        <figcaption><strong>Muhammad Imran</strong><span>SEO &amp; Founder</span></figcaption>
                    </figure>
                    <figure class="portfolio-page__testimonial home-results__testimonial" hidden>
                        <blockquote>
                            <p>“I am very grateful to the Trivo IT Solution team and mostly to Mr. Kashif Khan. He helped us select the right products for our needs. Proud to say, my portal was developed and designed by Trivo IT Solution.”</p>
                        </blockquote>
                        <figcaption><strong>Ronak Patel</strong><span>Director, United Air Travel &amp; Tours London UK</span></figcaption>
                    </figure>
                    <svg class="portfolio-page__review-arrow" viewBox="0 0 220 180" fill="none" aria-hidden="true">
                        <path d="M22 8C8 53 4 97 51 98c52 1 77-54 123-28 20 11 31 32 37 58" stroke="currentColor" stroke-dasharray="4 7" stroke-linecap="round"/>
                        <path d="m202 124 10 5 5-10" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="portfolio-page__review-proof">
                    <div class="portfolio-page__review-rating" aria-label="Five stars, 798 plus client reviews">
                        <span aria-hidden="true">★★★★★</span>
                        <small>798+ Client reviews</small>
                    </div>
                    <div class="portfolio-page__review-avatars" aria-hidden="true">
                        <img src="{{ asset('wp-content/uploads/2023/05/happy-carefree-dark-skinned-rebellious-young-woman-enjoys-awesome-music-makes-rock-n-roll-gesture-has-fun-on-music-festival-or-cool-event-wears-casual-jumper-poses-against-pink-wall-300x300.jpg') }}" alt="" width="40" height="40" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/pexels-andrea-piacquadio-3799837-300x300.jpg') }}" alt="" width="40" height="40" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/pexels-mikhail-nilov-9301512-300x300.jpg') }}" alt="" width="40" height="40" loading="lazy">
                    </div>
                    <a class="portfolio-page__reviews-link" href="https://share.google/V9Qv6dImscMPQSWGX" target="_blank" rel="noopener noreferrer">
                        View all reviews <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </section>

        @include('partials.home-clients', ['headingId' => 'portfolio-clients-title'])
    </article>
@endsection
