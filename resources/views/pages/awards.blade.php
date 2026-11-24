@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Awards - Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Award-winning digital marketing') : 'Award-winning digital marketing')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    @php
        $awards = [
            ['year' => 'Dubai', 'title' => 'Top 50 Tech Companies', 'provider' => 'InterCon', 'image' => 'wp-content/uploads/2025/03/Trivo-award-2.jpg', 'alt' => 'Top 50 Tech Companies Award from InterCon'],
            ['year' => 'India', 'title' => 'India 5000 Best MSME Awards', 'provider' => 'Ministry of MSME, Government of India', 'image' => 'wp-content/uploads/2025/03/Trivo-award-3-1.jpg', 'alt' => 'India 5000 Best MSME Awards'],
            ['year' => 'India, 2019', 'title' => 'Top 20 Travel Tech Solution Provider', 'provider' => 'CIO Reviews', 'image' => 'wp-content/uploads/2025/03/Trivo-award-4.jpg', 'alt' => 'Top 20 Travel Tech Solution Provider in India in 2019'],
            ['year' => '2021', 'title' => 'UpCity Best of Marketing & Advertising', 'provider' => 'UpCity', 'image' => 'wp-content/uploads/2023/06/new-badge20211006-5432-t7lh3l.png', 'alt' => 'UpCity Best of Marketing and Advertising Winner 2021'],
            ['year' => '2021', 'title' => 'Clutch Top Marketing Agency', 'provider' => 'Clutch', 'image' => 'wp-content/uploads/2023/06/56cf51c7d935aba26a8f553867bf878b.png', 'alt' => 'Clutch Top Marketing Agency 2021'],
            ['year' => '2020', 'title' => 'UpCity Top Ad Agency', 'provider' => 'UpCity', 'image' => 'wp-content/uploads/2023/06/new-badge20211005-28345-8m8kvp.png', 'alt' => 'UpCity Top Ad Agency Marketplace'],
        ];
    @endphp

    <article class="awards-page">
        <header class="awards-page__banner">
            <h1>Awards</h1>
        </header>

        <section class="awards-page__intro" aria-labelledby="awards-intro-title">
            <div class="awards-page__intro-copy">
                <h2 id="awards-intro-title">Award-winning digital marketing</h2>
                <p><strong>Success is not just our goal—it’s what we achieve!</strong> Our dedication to innovation and excellence has earned us industry recognition. We are committed to delivering impactful solutions that drive business growth and create lasting value.</p>
                <ul>
                    <li><strong>Innovative Solutions</strong> – We craft unique strategies that set brands apart.</li>
                    <li><strong>Data-Backed Approach</strong> – Every decision is driven by insights for maximum impact.</li>
                    <li><strong>Client-First Mindset</strong> – Your success is at the heart of everything we do.</li>
                </ul>
                <p>With a proven track record of delivering impactful digital marketing results, we help businesses grow, engage, and lead in their industry. <a href="{{ route('contact') }}">Let’s take your brand to new heights!</a></p>
            </div>
            <img src="{{ asset('wp-content/uploads/2025/03/Untitled-design-4.png') }}" alt="Digital marketing work at a laptop with analytics and communication icons" width="520" height="416" fetchpriority="high">
        </section>

        <section class="awards-page__timeline-section" aria-label="Trivo awards and recognition">
            <ol class="awards-page__timeline">
                @foreach ($awards as $award)
                    <li class="awards-page__item">
                        <figure>
                            <img src="{{ asset($award['image']) }}" alt="{{ $award['alt'] }}" width="360" height="360" loading="lazy">
                        </figure>
                        <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                        <div class="awards-page__item-copy">
                            <span>{{ $award['year'] }}</span>
                            <h2>{{ $award['title'] }}</h2>
                            <p>From <strong>{{ $award['provider'] }}</strong></p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="awards-page__cta" aria-labelledby="awards-cta-title">
            <img class="awards-page__google-logo" src="{{ asset('wp-content/uploads/2023/05/google-logo.svg') }}" alt="Google" width="70" height="24" loading="lazy">
            <span class="awards-page__partner-label">Partner</span>
            <h2 id="awards-cta-title">Start driving SEO results with Trivo</h2>
            <a href="{{ url('/free-audit') }}">Get a proposal <span aria-hidden="true">→</span></a>
            <img class="awards-page__rocket" src="{{ asset('wp-content/uploads/2023/05/GettyImages-1367732506-159x300.png') }}" alt="" width="90" height="170" loading="lazy">
        </section>

        @include('partials.home-clients', ['headingId' => 'awards-clients-title'])
    </article>
@endsection
