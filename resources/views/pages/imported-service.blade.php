@extends('layouts.public')

@section('title', $record->seo_title ?: $record->title.' | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('robots', $record->robots ?: config('seo.default_robots'))

@section('content')
    <article class="paid-search-page service-page service-page--{{ trim($record->path, '/') }} {{ in_array($record->path, \App\Services\Content\ServicePageLayout::LOCATION_PATHS, true) ? 'location-page' : '' }}">
        <header class="paid-search-heading">
            @if ($record->path === '/paid-search-marketing')
                <div class="paid-search-hero__copy">
                    <span class="paid-search-hero__eyebrow"><span aria-hidden="true"></span> PERFORMANCE MARKETING</span>
                    <h1>{{ $record->title }}</h1>
                    <p>Show up when it matters. Turn high-intent searches into your next best customers.</p>
                    <a class="paid-search-hero__cta" href="{{ route('contact') }}">Let’s plan your campaign <span aria-hidden="true">↗</span></a>
                </div>
                <div class="paid-search-hero__visual" aria-hidden="true">
                    <div class="paid-search-hero__orbit paid-search-hero__orbit--one"></div>
                    <div class="paid-search-hero__orbit paid-search-hero__orbit--two"></div>
                    <div class="paid-search-hero__ad-card">
                        <div class="paid-search-hero__ad-top"><span class="paid-search-hero__google-mark">G</span><span>Search campaign</span><span class="paid-search-hero__live"><i></i> LIVE</span></div>
                        <div class="paid-search-hero__search"><span>⌕</span> marketing that moves you forward <b>⌕</b></div>
                        <div class="paid-search-hero__sponsored">SPONSORED · trivo.in</div>
                        <strong>Make every click count</strong>
                        <p>Reach the right people at the right moment.</p>
                        <div class="paid-search-hero__ad-foot"><span>Relevant reach</span><span>Clear results <b>↗</b></span></div>
                    </div>
                    <div class="paid-search-hero__spark paid-search-hero__spark--one">↗</div>
                    <div class="paid-search-hero__spark paid-search-hero__spark--two">✦</div>
                </div>
                <div class="paid-search-hero__ticker" aria-hidden="true"><div class="paid-search-hero__ticker-track"><span>SEARCH WITH INTENT</span><i>✳</i><span>SMARTER SPEND</span><i>✳</i><span>REAL GROWTH</span><i>✳</i><span>SEARCH WITH INTENT</span><i>✳</i><span>SMARTER SPEND</span><i>✳</i><span>REAL GROWTH</span><i>✳</i></div></div>
            @else
                <h1>{{ $record->title }}</h1>
            @endif
        </header>
        @if ($record->path === '/search-engine-optimization')
            <div class="seo-reference-design">
                <section class="seo-reference-hero" aria-labelledby="seo-reference-title">
                    <div class="seo-reference-container seo-reference-hero__inner">
                        <div class="seo-reference-hero__copy">
                            <span class="seo-reference-eyebrow"><i aria-hidden="true"></i> Search engine optimisation</span>
                            <h1 id="seo-reference-title">Rank higher.<br>Grow <span>organically.</span></h1>
                            <p>Our SEO experts use strategic, data-driven techniques to help your website rank higher on Google, Bing and Yahoo — attracting more organic traffic, generating leads and increasing revenue.</p>
                            <div class="seo-reference-actions">
                                <a class="seo-reference-button" href="{{ url('/free-audit') }}">Start growing <span aria-hidden="true">→</span></a>
                                <a class="seo-reference-button seo-reference-button--outline" href="#seo-reference-services">Our services</a>
                            </div>
                        </div>

                        <div class="seo-reference-serp" aria-label="Illustration of improved search rankings">
                            <div class="seo-reference-serp__search"><span aria-hidden="true">⌕</span> best services near me</div>
                            <div class="seo-reference-serp__result seo-reference-serp__result--first">
                                <div class="seo-reference-serp__domain">yourbusiness.com <b>#1</b></div>
                                <div class="seo-reference-serp__bar"></div><div class="seo-reference-serp__bar seo-reference-serp__bar--sub"></div>
                            </div>
                            <div class="seo-reference-serp__result"><div class="seo-reference-serp__domain">competitor.com</div><div class="seo-reference-serp__bar seo-reference-serp__bar--other"></div><div class="seo-reference-serp__bar seo-reference-serp__bar--sub"></div></div>
                            <div class="seo-reference-serp__result"><div class="seo-reference-serp__domain">competitor.com</div><div class="seo-reference-serp__bar seo-reference-serp__bar--other"></div><div class="seo-reference-serp__bar seo-reference-serp__bar--sub"></div></div>
                            <div class="seo-reference-stat"><strong>+240%</strong><span>organic traffic</span></div>
                        </div>
                    </div>
                </section>

                <section class="seo-reference-values" aria-label="SEO benefits">
                    <div class="seo-reference-container"><span>Higher search rankings</span><span>Increased website traffic</span><span>Better user experience</span><span>Long-term growth</span></div>
                </section>

                <section class="seo-reference-services" id="seo-reference-services" aria-labelledby="seo-reference-services-title">
                    <div class="seo-reference-container">
                        <div class="seo-reference-section-heading">
                            <h2 id="seo-reference-services-title">Our complete<br>SEO services</h2>
                            <p>A full range of SEO services designed to improve your visibility, drive organic traffic and increase conversions — tailored to your goals and continuously optimised.</p>
                        </div>
                        <div class="seo-reference-service-grid">
                            <article><span>01</span><h3>On-Page SEO</h3><p>Optimising content, meta tags and site structure.</p><ul><li>Keyword research &amp; optimisation</li><li>SEO-friendly content creation</li><li>Meta tags, headers &amp; URL structuring</li><li>Image optimisation &amp; internal linking</li></ul></article>
                            <article><span>02</span><h3>Off-Page SEO</h3><p>High-quality backlinks that build authority.</p><ul><li>High-quality link building</li><li>Guest blogging &amp; outreach</li><li>Social media &amp; brand mentions</li><li>Google Business Profile optimisation</li></ul></article>
                            <article><span>03</span><h3>Technical SEO</h3><p>Speed, mobile-friendliness and indexing.</p><ul><li>Website speed &amp; mobile optimisation</li><li>Fixing broken links &amp; redirects</li><li>Schema markup implementation</li><li>Secure HTTPS &amp; Core Web Vitals</li></ul></article>
                            <article><span>04</span><h3>Local SEO</h3><p>Rank in local searches and on Google Maps.</p><ul><li>Local keyword targeting</li><li>Map pack visibility</li><li>Citations &amp; listings</li><li>Review strategy</li></ul></article>
                        </div>
                    </div>
                </section>

                <section class="seo-reference-results" aria-labelledby="seo-reference-results-title">
                    <div class="seo-reference-container">
                        <span class="seo-reference-kicker">How SEO transforms your business</span>
                        <h2 id="seo-reference-results-title">How our SEO services help grow<br>your revenue</h2>
                        <div class="seo-reference-results-grid">
                            <article><b>01</b><h3>Higher Google rankings</h3><p>Appear in front of potential customers at the right time.</p></article>
                            <article><b>02</b><h3>More qualified leads</h3><p>Attract visitors who are ready to take action.</p></article>
                            <article><b>03</b><h3>Better brand credibility</h3><p>Establish your business as an industry leader.</p></article>
                            <article><b>04</b><h3>Increased website traffic</h3><p>Drive consistent, high-quality visitors to your site.</p></article>
                            <article><b>05</b><h3>Cost-effective marketing</h3><p>Get long-term results without ongoing ad spend.</p></article>
                            <article><b>06</b><h3>Higher conversion rates</h3><p>Optimise your site for better user experience and engagement.</p></article>
                        </div>
                    </div>
                </section>

                <section class="seo-reference-why" aria-label="Why SEO and why Trivo">
                    <div class="seo-reference-container">
                        <article class="seo-reference-why__card">
                            <h2>Why SEO matters for your business</h2>
                            <p>SEO puts you in front of customers who are actively searching for your services. Most users click on first-page results — higher rankings mean more traffic and more trust.</p>
                            <ul><li>Increases website visibility</li><li>Builds trust and credibility</li><li>Drives organic traffic</li><li>Generates long-term results</li></ul>
                        </article>
                        <article class="seo-reference-why__card seo-reference-why__card--lime">
                            <h2>Why choose Trivo for SEO?</h2>
                            <ol><li>Data-driven SEO strategies</li><li>Experienced SEO experts</li><li>Transparent reporting</li><li>Customised solutions</li><li>Focus on real business growth</li></ol>
                        </article>
                    </div>
                </section>
            </div>
            <div class="seo-reference-source-content">{!! $bodyHtml !!}</div>
        @else
            {!! $bodyHtml !!}
        @endif
    </article>
@endsection
