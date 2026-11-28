@extends('layouts.public')

@section('title', 'Digital Marketing Services | SEO, PPC, Social Media & Web')
@section('description', 'Explore Trivo growth services including SEO, PPC, social media marketing, website strategy and conversion-focused web design.')

@section('content')
    <main class="services-redesign">
        <section class="services-hero" aria-labelledby="services-hero-title">
            <div class="services-container">
                <span class="services-eyebrow"><i aria-hidden="true"></i> Our services</span>
                <div class="services-hero-grid">
                    <h1 id="services-hero-title">Marketing that<br>turns <span>attention</span> into<br>revenue.</h1>
                    <div class="services-hero-copy">
                        <p>From search and social to landing page optimisation, we build the systems that move qualified leads from interest to action.</p>
                        <a class="services-primary-link" href="#services-list">Explore services <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
                <div class="services-stats" aria-label="Trivo at a glance">
                    <div><strong>10+</strong><span>Years in growth marketing</span></div>
                    <div><strong>350+</strong><span>Brands scaled</span></div>
                    <div><strong>4.7★</strong><span>Average client rating</span></div>
                    <div><strong>3.2x</strong><span>Average return on ad spend</span></div>
                </div>
            </div>
            <span class="services-hero-glow" aria-hidden="true"></span>
        </section>

        <section class="services-list-section" id="services-list" aria-labelledby="services-list-title">
            <div class="services-container">
                <div class="services-section-heading">
                    <h2 id="services-list-title">What we do best</h2>
                    <p>Four focused disciplines, one connected growth engine.</p>
                </div>

                @php
                    $services = [
                        ['number' => '01', 'icon' => 'search', 'meta' => 'Organic growth', 'title' => 'SEO strategy', 'copy' => 'Improve visibility, outrank competitors, and turn high-intent traffic into a steady pipeline of qualified leads.', 'tags' => ['Technical audits', 'Content & keyword plans', 'Local SEO'], 'url' => '/search-engine-optimization'],
                        ['number' => '02', 'icon' => 'cursor', 'meta' => 'PPC', 'title' => 'Paid search', 'copy' => 'Launch and optimise Google Ads and search campaigns that generate faster acquisition and clearer conversion data.', 'tags' => ['Google Ads', 'Shopping campaigns', 'Bid & budget tuning'], 'url' => '/paid-search-marketing'],
                        ['number' => '03', 'icon' => 'megaphone', 'meta' => 'Brand + performance', 'title' => 'Social media marketing', 'copy' => 'Create campaigns that build attention, community trust and revenue from the channels your audience already uses.', 'tags' => ['Meta & LinkedIn ads', 'Creative production', 'Community growth'], 'url' => '/social-media-marketing'],
                        ['number' => '04', 'icon' => 'layout', 'meta' => 'Conversion design', 'title' => 'Web design & CRO', 'copy' => 'Strengthen your website experience with landing pages and UX improvements that reduce friction and increase conversions.', 'tags' => ['Landing pages', 'A/B testing', 'UX audits'], 'url' => '/rate-optimization'],
                    ];
                @endphp

                <div class="services-rows">
                    @foreach ($services as $service)
                        <article class="services-row">
                            <span class="services-row-number">{{ $service['number'] }}</span>
                            <div class="services-row-title">
                                <span class="services-row-icon" aria-hidden="true">
                                    @if ($service['icon'] === 'search')
                                        <svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.4"></circle><path d="m15.5 15.5 4.3 4.3"></path></svg>
                                    @elseif ($service['icon'] === 'cursor')
                                        <svg viewBox="0 0 24 24"><path d="m5 3 14 9-7 1-3 7L5 3Z"></path><path d="m12 13 5 6"></path></svg>
                                    @elseif ($service['icon'] === 'megaphone')
                                        <svg viewBox="0 0 24 24"><path d="m3 11 15-6v14L3 13v-2Z"></path><path d="m7 14 2 6h4l-2-4M21 9v6"></path></svg>
                                    @else
                                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="7" rx="1"></rect><rect x="3" y="14" width="8" height="6" rx="1"></rect><rect x="14" y="14" width="7" height="6" rx="1"></rect></svg>
                                    @endif
                                </span>
                                <span class="services-row-meta">{{ $service['meta'] }}</span>
                                <h3>{{ $service['title'] }}</h3>
                            </div>
                            <div class="services-row-detail">
                                <p>{{ $service['copy'] }}</p>
                                <ul aria-label="{{ $service['title'] }} capabilities">
                                    @foreach ($service['tags'] as $tag)
                                        <li>{{ $tag }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <a class="services-circle-link" href="{{ url($service['url']) }}" aria-label="Explore {{ $service['title'] }}"><span aria-hidden="true">↗</span></a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="services-process" aria-labelledby="services-process-title">
            <div class="services-container">
                <span class="services-eyebrow services-eyebrow--plain">How we work</span>
                <h2 id="services-process-title">A simple process, built to<br>compound.</h2>
                <div class="services-process-grid">
                    <article><span>01</span><h3>Discover</h3><p>Audit your channels, funnel and competitors to find where growth is hiding.</p></article>
                    <article><span>02</span><h3>Plan</h3><p>Build a channel strategy tied directly to revenue goals, not vanity metrics.</p></article>
                    <article><span>03</span><h3>Launch</h3><p>Ship campaigns, pages and creative fast, with tracking in place from day one.</p></article>
                    <article><span>04</span><h3>Optimise</h3><p>Weekly reporting and testing loops that compound results month after month.</p></article>
                </div>
            </div>
        </section>

        <section class="services-deliver-section" aria-labelledby="services-deliver-title">
            <div class="services-container">
                <div class="services-deliver-banner">
                    <div class="services-deliver-copy">
                        <span class="services-eyebrow services-eyebrow--gold">What we deliver</span>
                        <h2 id="services-deliver-title">A partner for strategy, execution, and measurable growth.</h2>
                        <a href="{{ route('contact') }}">Book a free strategy call <span aria-hidden="true">↗</span></a>
                    </div>
                    <ul>
                        <li>Channel strategy aligned to revenue goals</li>
                        <li>High-converting landing page design</li>
                        <li>Performance reporting and optimisation loops</li>
                        <li>Creative testing for awareness and acquisition</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
@endsection
