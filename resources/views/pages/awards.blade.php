@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Awards & Recognition | Trivo')
@section('description', isset($record) ? ($record->seo_description ?: '') : 'Explore the awards and recognition Trivo has received for technology, digital marketing, and client-focused solutions.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="awards-page awards-page--redesign">
        <header class="awards-page__banner">
            <span class="awards-page__banner-orb" aria-hidden="true"></span>
            <h1>Awards</h1>
        </header>

        <section class="awards-page__intro" aria-labelledby="awards-intro-title">
            <div class="awards-page__intro-copy">
                <span class="awards-page__eyebrow">Recognition &amp; milestones</span>
                <h2 id="awards-intro-title">Award-winning <span>digital marketing</span></h2>
                <p><strong>Success is not just our goal—it’s what we achieve!</strong> Our dedication to innovation and excellence has earned us industry recognition. We are committed to delivering impactful solutions that drive business growth and create lasting value.</p>
                <ul>
                    <li><strong>Innovative Solutions</strong><span>We craft unique strategies that set brands apart.</span></li>
                    <li><strong>Data-Backed Approach</strong><span>Every decision is driven by insights for maximum impact.</span></li>
                    <li><strong>Client-First Mindset</strong><span>Your success is at the heart of everything we do.</span></li>
                </ul>
                <p class="awards-page__intro-note">With a proven track record of delivering impactful digital marketing results, we help businesses grow, engage, and lead in their industry. <a href="{{ url('/contact') }}">Let’s take your brand to new heights <span aria-hidden="true">→</span></a></p>
            </div>
            <div class="awards-page__intro-visual">
                <span class="awards-page__visual-orb awards-page__visual-orb--one" aria-hidden="true"></span>
                <span class="awards-page__visual-orb awards-page__visual-orb--two" aria-hidden="true"></span>
                <img
                    src="{{ asset('wp-content/uploads/2025/03/Untitled-design-4.png') }}"
                    alt="Digital marketing work with analytics and communication icons"
                    width="520"
                    height="416"
                    fetchpriority="high"
                >
            </div>
        </section>

        <section class="awards-page__recognition" aria-labelledby="awards-recognition-title">
            <div class="awards-page__section-heading">
                <span class="awards-page__eyebrow">The work. The impact. The recognition.</span>
                <h2 id="awards-recognition-title">Awards &amp; Recognition</h2>
                <span class="awards-page__heading-line" aria-hidden="true"></span>
            </div>

            <ol class="awards-page__timeline" aria-label="Trivo awards and recognition">
                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2025/03/Trivo-award-2.jpg') }}" alt="Top 50 Tech Companies Award from InterCon" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">⌖</span> Dubai</span>
                        <h3>Top 50 Tech Companies</h3>
                        <p>Recognised by <strong>InterCon</strong></p>
                    </div>
                </li>

                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2025/03/Trivo-award-3-1.jpg') }}" alt="India 5000 Best MSME Awards" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">⌖</span> India</span>
                        <h3>India 5000 Best MSME Awards</h3>
                        <p>Recognised by <strong>Ministry of MSME, Government of India</strong></p>
                    </div>
                </li>

                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2025/03/Trivo-award-4.jpg') }}" alt="Top 20 Travel Tech Solution Provider award" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">⌖</span> India, 2019</span>
                        <h3>Top 20 Travel Tech Solution Provider</h3>
                        <p>Recognised by <strong>CIO Reviews</strong></p>
                    </div>
                </li>

                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2023/06/new-badge20211006-5432-t7lh3l.png') }}" alt="UpCity Best of Marketing and Advertising Winner 2021" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">▦</span> 2021</span>
                        <h3>UpCity Best of Marketing &amp; Advertising</h3>
                        <p>Recognised by <strong>UpCity</strong></p>
                    </div>
                </li>

                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2023/06/56cf51c7d935aba26a8f553867bf878b.png') }}" alt="Clutch Top Marketing Agency 2021" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">▦</span> 2021</span>
                        <h3>Clutch Top Marketing Agency</h3>
                        <p>Recognised by <strong>Clutch</strong></p>
                    </div>
                </li>

                <li class="awards-page__item">
                    <figure>
                        <img src="{{ asset('wp-content/uploads/2023/06/new-badge20211005-28345-8m8kvp.png') }}" alt="UpCity Top Ad Agency Marketplace recognition" width="360" height="360" loading="lazy">
                    </figure>
                    <span class="awards-page__timeline-dot" aria-hidden="true"></span>
                    <div class="awards-page__item-copy">
                        <span class="awards-page__item-meta"><span aria-hidden="true">▦</span> 2020</span>
                        <h3>UpCity Top Ad Agency</h3>
                        <p>Recognised by <strong>UpCity</strong></p>
                    </div>
                </li>
            </ol>
        </section>

        <section class="awards-page__cta" aria-labelledby="awards-cta-title">
            <div class="awards-page__cta-partner">
                <img class="awards-page__google-logo" src="{{ asset('wp-content/uploads/2023/05/google-logo.svg') }}" alt="Google" width="70" height="24" loading="lazy">
                <span>Partner</span>
            </div>
            <div class="awards-page__cta-copy">
                <span class="awards-page__eyebrow">Ready for your next milestone?</span>
                <h2 id="awards-cta-title">Start driving SEO results with Trivo</h2>
                <p>Build a stronger digital presence with practical strategies focused on visibility, traffic, and growth.</p>
                <a href="{{ url('/free-audit') }}">Get a proposal <span aria-hidden="true">→</span></a>
            </div>
            <img class="awards-page__rocket" src="{{ asset('wp-content/uploads/2023/05/GettyImages-1367732506-159x300.png') }}" alt="" width="90" height="170" loading="lazy">
        </section>

        @include('partials.home-clients', ['headingId' => 'awards-clients-title'])
    </article>
@endsection
