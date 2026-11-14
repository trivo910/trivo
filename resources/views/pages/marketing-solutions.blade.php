@extends('layouts.public')

@section('title', $record->seo_title ?: $record->title.' | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('robots', $record->robots ?: config('seo.default_robots'))

@section('content')
    @php
        $solutions = [
            [
                'title' => 'Paid Search Marketing',
                'copy' => 'Paid Search Marketing is a digital advertising strategy where advertisers pay search engines like Google and Bing to display their ads on search engine results pages. These ads usually appear with labels like “Sponsored” or “Ad.”',
                'image' => 'wp-content/uploads/2026/03/12084760_20943904-Photoroom.png',
                'alt' => 'Paid search campaign performance illustration',
                'url' => '/paid-search-marketing',
            ],
            [
                'title' => 'Social Media Marketing',
                'copy' => 'Social media marketing creates and shares content across platforms such as Facebook, Instagram, Twitter, LinkedIn, TikTok, and YouTube to engage audiences and promote your business through organic and paid activity.',
                'image' => 'wp-content/uploads/2026/03/social-media-marketing-design-concept-with-people-smartphone-flat-illustration_559317-1986.png',
                'alt' => 'People creating social media content on a smartphone',
                'url' => '/social-media-marketing',
            ],
            [
                'title' => 'Search Engine Optimization',
                'copy' => 'SEO improves your website’s visibility in unpaid search results. We combine technical improvements, useful content, and relevant links to attract qualified organic traffic and strengthen your rankings.',
                'image' => 'wp-content/uploads/2026/03/SEO-Strategy.png',
                'alt' => 'SEO strategy illustration with search and analytics tools',
                'url' => '/search-engine-optimization',
            ],
            [
                'title' => 'Google Shopping',
                'copy' => 'Showcase products with images, prices, and relevant details directly in Google search results. We help businesses set up product feeds and campaigns through Google Merchant Center and Google Ads.',
                'url' => '/google-shopping',
            ],
            [
                'title' => 'Email Marketing',
                'copy' => 'Reach prospects and customers with timely, relevant email campaigns. Build stronger relationships and encourage conversions with targeted messages informed by customer interests and behaviour.',
                'url' => '/email-marketing',
            ],
            [
                'title' => 'Performance Marketing',
                'copy' => 'Run measurable campaigns across channels such as search, social, and display. We use clear performance metrics to optimise spend and improve results over time.',
                'url' => '/performance-marketing-agency-nagpur',
            ],
            [
                'title' => 'Graphic Design',
                'copy' => 'Build a consistent visual identity with creative for marketing, advertising, web design, and products. Thoughtful design helps your brand communicate clearly and stand out.',
                'url' => '/graphic-design',
            ],
            [
                'title' => 'Amazon Shopping',
                'copy' => 'Grow your ecommerce presence with stronger Amazon product listings and campaigns. We help brands improve product visibility, reach shoppers, and support sales on the marketplace.',
                'url' => '/amazon-shopping',
            ],
        ];
    @endphp

    <article class="marketing-solutions-page">
        <h1 class="marketing-solutions-page__title">Our Marketing Solutions</h1>

        <div class="marketing-solutions-list">
            @foreach ($solutions as $solution)
                <section class="marketing-solution" aria-labelledby="marketing-solution-{{ $loop->index }}">
                    <div class="marketing-solution__copy">
                        <h2 id="marketing-solution-{{ $loop->index }}">{{ $solution['title'] }}</h2>
                        <p>{{ $solution['copy'] }}</p>
                        <a href="{{ url($solution['url']) }}" class="marketing-solution__link">
                            Read more <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    @if (isset($solution['image']))
                        <div class="marketing-solution__media">
                            <img
                                src="{{ asset($solution['image']) }}"
                                alt="{{ $solution['alt'] }}"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                @if ($loop->first) fetchpriority="high" @endif
                            >
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </article>
@endsection
