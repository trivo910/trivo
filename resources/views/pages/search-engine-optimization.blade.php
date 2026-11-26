@extends('layouts.public')

@section('title', 'Search Engine Optimization Services | Trivo – Rank Higher, Grow Faster')
@section('description', 'Our SEO experts implement strategic, data-driven techniques to help your website attract more organic traffic, generate leads, and increase revenue.')

@section('content')
<article class="seo-landing-page">
    {{-- HERO --}}
    <section class="seo-hero" aria-labelledby="seo-page-title">
        <div class="seo-container">
            <div class="seo-hero__heading">
                <span class="seo-eyebrow">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg>
                    SEO Services
                </span>
                <h1 id="seo-page-title">Search engine optimization</h1>
                <span class="seo-heading-underline" aria-hidden="true"></span>
            </div>

            <div class="seo-hero__grid">
                <div class="seo-hero__copy">
                    <p class="seo-kicker">Grow your visibility. Win better customers.</p>
                    <h2>Dominate Search Rankings <span>&amp; Drive Organic Growth</span></h2>
                    <p class="seo-body-copy">In today’s digital landscape, Search Engine Optimization (SEO) is essential for businesses looking to rank higher on Google, Bing, and Yahoo. Our SEO experts implement strategic, data-driven techniques to help your website attract more organic traffic, generate leads, and increase revenue.</p>
                    <a class="seo-button" href="{{ url('/free-audit') }}">
                        Get a Free SEO Audit
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M13 6l6 6-6 6"></path></svg>
                    </a>
                </div>

                <div class="seo-hero__benefits">
                    <h2>Why Invest in SEO?</h2>
                    <ul class="seo-check-list">
                        <li>Higher Search Rankings</li>
                        <li>Increased Website Traffic</li>
                        <li>Better User Experience</li>
                        <li>Long-Term Growth</li>
                    </ul>
                    <div class="seo-hero__mini-proof" aria-hidden="true">
                        <span>Visibility</span><span>Traffic</span><span>Growth</span>
                    </div>
                </div>

                <div class="seo-hero__art">
                    <span class="seo-orbit seo-orbit--one" aria-hidden="true"></span>
                    <span class="seo-orbit seo-orbit--two" aria-hidden="true"></span>
                    <img src="{{ asset('wp-content/uploads/2026/03/team-seo-working.png') }}" alt="SEO specialists reviewing search performance" width="620" height="380" fetchpriority="high">
                    <span class="seo-art-bubble seo-art-bubble--one" aria-hidden="true">↗</span>
                    <span class="seo-art-bubble seo-art-bubble--two" aria-hidden="true">SEO</span>
                </div>
            </div>
        </div>
    </section>

    {{-- COMPLETE SEO SERVICES --}}
    <section class="seo-services seo-section" aria-labelledby="seo-services-title">
        <div class="seo-container">
            <div class="seo-section-heading">
                <span class="seo-kicker">What we do</span>
                <h2 id="seo-services-title">Our Complete SEO Services</h2>
                <p>We offer a full range of SEO services designed to improve your website’s visibility, drive organic traffic, and increase conversions. Our strategies are tailored to your business goals and continuously optimized for better results.</p>
            </div>

            <div class="seo-service-grid">
                <article class="seo-service-card seo-service-card--blue">
                    <span class="seo-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 8h18M8 4v4"></path></svg>
                    </span>
                    <h3>On-Page SEO</h3>
                    <ul class="seo-feature-list">
                        <li>Keyword Research &amp; Optimization</li>
                        <li>SEO-Friendly Content Creation</li>
                        <li>Meta Tags, Headers &amp; URL Structuring</li>
                        <li>Image Optimization &amp; Internal Linking</li>
                    </ul>
                </article>

                <article class="seo-service-card seo-service-card--purple">
                    <span class="seo-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M10 13.5 14 9.5M8.2 15.3l-1.4 1.4a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0M15.8 8.7l1.4-1.4a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0"></path></svg>
                    </span>
                    <h3>Off-Page SEO</h3>
                    <ul class="seo-feature-list">
                        <li>High-Quality Link Building</li>
                        <li>Guest Blogging &amp; Outreach</li>
                        <li>Social Media &amp; Brand Mentions</li>
                        <li>Local SEO &amp; Google Business Profile Optimization</li>
                    </ul>
                </article>

                <article class="seo-service-card seo-service-card--gold">
                    <span class="seo-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.7-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.7 1.4 2.4-1.4 1.1a7 7 0 0 1 0 1.7Z"></path></svg>
                    </span>
                    <h3>Technical SEO</h3>
                    <ul class="seo-feature-list">
                        <li>Website Speed &amp; Mobile Optimization</li>
                        <li>Fixing Broken Links &amp; Redirects</li>
                        <li>Schema Markup Implementation</li>
                        <li>Secure HTTPS &amp; Core Web Vitals Optimization</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- HOW SEO TRANSFORMS YOUR BUSINESS --}}
    <section class="seo-feature-band seo-feature-band--first" aria-labelledby="seo-transform-title">
        <div class="seo-container seo-feature-band__inner">
            <div class="seo-feature-band__media">
                <img src="{{ asset('wp-content/uploads/2025/03/13549607_5287968-1.jpg') }}" alt="Illustration representing search growth and SEO performance" width="2000" height="2000" loading="lazy">
            </div>
            <div class="seo-feature-band__copy">
                <span class="seo-kicker">Built for sustainable growth</span>
                <h2 id="seo-transform-title">How SEO Transforms Your Business</h2>
                <p>Our search engine optimization services help turn search visibility into meaningful business outcomes.</p>
                <ul class="seo-arrow-list">
                    <li><strong>Higher Google Rankings</strong><span>Appear in front of potential customers at the right time.</span></li>
                    <li><strong>More Qualified Leads</strong><span>Attract visitors who are ready to take action.</span></li>
                    <li><strong>Better Brand Credibility</strong><span>Establish your business as an industry leader.</span></li>
                    <li><strong>Increased Website Traffic</strong><span>Drive consistent, high-quality visitors to your site.</span></li>
                    <li><strong>Cost-Effective Marketing</strong><span>Get long-term results without ongoing ad spend.</span></li>
                    <li><strong>Higher Conversion Rates</strong><span>Optimize your site for better user experience and engagement.</span></li>
                </ul>
            </div>
        </div>
    </section>

    {{-- WHY SEO MATTERS --}}
    <section class="seo-feature-band seo-feature-band--reverse" aria-labelledby="seo-importance-title">
        <div class="seo-container seo-feature-band__inner">
            <div class="seo-feature-band__copy">
                <span class="seo-kicker">Be found when it matters</span>
                <h2 id="seo-importance-title">Why SEO is Important for Your Business?</h2>
                <p>SEO helps your business appear in front of customers who are actively searching for your services. Higher rankings lead to increased visibility, more traffic, and better trust among users. Most users click on results on the first page, making SEO essential for online success.</p>
                <ul class="seo-check-list seo-check-list--compact">
                    <li>Increases website visibility</li>
                    <li>Builds trust and credibility</li>
                    <li>Drives organic traffic</li>
                    <li>Generates long-term results</li>
                </ul>
            </div>
            <div class="seo-feature-band__media">
                <img src="{{ asset('wp-content/uploads/2026/03/13399753_Work_7-scaled.jpg') }}" alt="Illustration showing improved search visibility" width="2560" height="2560" loading="lazy">
            </div>
        </div>
    </section>

    {{-- SEO TYPES --}}
    <section class="seo-types seo-section" aria-labelledby="seo-types-title">
        <div class="seo-container">
            <div class="seo-section-heading seo-section-heading--compact">
                <span class="seo-kicker">A complete approach</span>
                <h2 id="seo-types-title">Types of SEO We Offer</h2>
            </div>
            <div class="seo-type-grid">
                <article class="seo-type-card seo-type-card--blue">
                    <span class="seo-type-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Z"></path><path d="m3 12 9 5 9-5M3 16l9 5 9-5"></path></svg></span>
                    <h3>On-Page SEO</h3>
                    <p>Optimize your website’s content and structure for higher rankings.</p>
                </article>
                <article class="seo-type-card seo-type-card--purple">
                    <span class="seo-type-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m10 14 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0M16 8l1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0"></path></svg></span>
                    <h3>Off-Page SEO</h3>
                    <p>Build authority with quality backlinks and brand mentions.</p>
                </article>
                <article class="seo-type-card seo-type-card--gold">
                    <span class="seo-type-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19 13.5 21 15l-2 4-2.3-.7-1.7 1.2-.4 2.5h-4.5l-.4-2.5-1.7-1.2L5.7 19l-2-4 2-1.5v-2L3.7 10l2-4 2.3.7 1.7-1.2.4-2.5h4.5l.4 2.5 1.7 1.2 2.3-.7 2 4-2 1.5v2Z"></path></svg></span>
                    <h3>Technical SEO</h3>
                    <p>Improve site performance, indexing and overall health.</p>
                </article>
                <article class="seo-type-card seo-type-card--green">
                    <span class="seo-type-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg></span>
                    <h3>Local SEO</h3>
                    <p>Boost your visibility in local searches and on Google Maps.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- WHY CHOOSE TRIVO --}}
    <section class="seo-choose" aria-labelledby="seo-choose-title">
        <div class="seo-container seo-choose__inner">
            <div class="seo-choose__copy">
                <span class="seo-kicker">Your growth partner</span>
                <h2 id="seo-choose-title">Why Choose Trivo for SEO?</h2>
                <ul class="seo-check-list seo-check-list--compact">
                    <li>Data-driven SEO strategies</li>
                    <li>Experienced SEO experts</li>
                    <li>Transparent reporting</li>
                    <li>Customized solutions</li>
                    <li>Focus on real business growth</li>
                </ul>
            </div>
            <div class="seo-choose__media">
                <img src="{{ asset('wp-content/uploads/2026/03/team-seo-working.png') }}" alt="Trivo team collaborating on SEO and digital growth" width="620" height="380" loading="lazy">
            </div>
        </div>
    </section>

    {{-- CLIENTS --}}
    @php
        $seoClients = [
            ['Sanjari-2.png', 'Sanjari'],
            ['Polla-Tech.png', 'Polla Tech'],
            ['Al-Bushra.png', 'Al Bushra'],
            ['Mumbai-Darshan.png', 'Mumbai Darshan'],
            ['Stock-Sovet.png', 'Stock Sovet'],
            ['Stop-Over.png', 'Stop Over'],
            ['Quba-Dates.png', 'Quba Dates'],
            ['Medikash.png', 'Medikash'],
            ['MYVN.png', 'MYVN'],
            ['Al-Arafat.png', 'Al-Arafat'],
            ['Al-Rida.png', 'Al-Rida'],
            ['Gulshan-Tours.png', 'Gulshan Tours'],
            ['Global-Duniya.png', 'Global Duniya'],
        ];
    @endphp
    <section class="seo-clients" aria-labelledby="seo-clients-title">
        <div class="seo-container">
            <div class="seo-section-heading seo-section-heading--compact">
                <span class="seo-kicker">Trusted by growing businesses</span>
                <h2 id="seo-clients-title">Clients We Work With</h2>
            </div>
            <div class="seo-clients__track" aria-label="Trivo clients">
                @foreach ($seoClients as [$image, $name])
                    <div class="seo-client-logo"><img src="{{ asset('wp-content/uploads/2026/03/'.$image) }}" alt="{{ $name }}" width="300" height="135" loading="lazy"></div>
                @endforeach
            </div>
        </div>
    </section>
</article>
@endsection
