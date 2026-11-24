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
        {!! $bodyHtml !!}
    </article>
@endsection
