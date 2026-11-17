@extends('layouts.public')
@section('marketingLayout', 'true')
@section('title', $portfolio->seo_title ?: $portfolio->title.' | Trivo')
@section('description', $portfolio->seo_description)
@section('canonical', $portfolio->canonical_url ?: url($portfolio->path))
@section('robots', $portfolio->robots ?: 'index,follow')
@section('socialMeta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $portfolio->og_title ?: $portfolio->seo_title ?: $portfolio->title }}">
    <meta property="og:description" content="{{ $portfolio->og_description ?: $portfolio->seo_description }}">
    <meta property="og:url" content="{{ $portfolio->canonical_url ?: url($portfolio->path) }}">
    @if(isset($images[$portfolio->og_image_id ?: ($layout['thumbnail_media_id'] ?? 0)]))<meta property="og:image" content="{{ $images[$portfolio->og_image_id ?: $layout['thumbnail_media_id']]['url'] }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $portfolio->twitter_title ?: $portfolio->seo_title ?: $portfolio->title }}">
    <meta name="twitter:description" content="{{ $portfolio->twitter_description ?: $portfolio->seo_description }}">
    @if(isset($images[$portfolio->twitter_image_id ?: ($layout['thumbnail_media_id'] ?? 0)]))<meta name="twitter:image" content="{{ $images[$portfolio->twitter_image_id ?: $layout['thumbnail_media_id']]['url'] }}">@endif
@endsection
@section('content')
<article class="case-study case-design--{{ $layout['design'] ?? 'modern' }}" style="--case-bg: {{ $layout['background'] }}; --case-start: {{ $layout['gradient_start'] }}; --case-end: {{ $layout['gradient_end'] }}; --case-heading: {{ $layout['heading_color'] }}; --case-text: {{ $layout['body_color'] }}; --case-width: {{ $layout['container_width'] }}px">
    @foreach($layout['sections'] as $section)
        @continue(!($section['visible'] ?? true))
        @php($image = $images[$section['image_id'] ?? 0] ?? null)
        <section class="case-section case-section--{{ $section['type'] }}" style="--section-top: {{ $section['padding_top'] }}px; --section-bottom: {{ $section['padding_bottom'] }}px">
            <div class="case-container">
                @if(in_array($section['type'], ['hero', 'report']))
                    <div class="case-split case-split--image-{{ $section['image_position'] }}">
                        <div class="case-copy">
                            @if(!empty($section['eyebrow']))<p class="case-eyebrow">{{ $section['eyebrow'] }}</p>@endif
                            @if($section['type'] === 'hero')<h1>{{ $section['title'] }}</h1>@elseif(!($section['hide_title'] ?? false))<h2>@if(!empty($section['heading_url']))<a href="{{ $section['heading_url'] }}">{{ $section['title'] }}</a>@else{{ $section['title'] }}@endif</h2>@endif
                            <div class="case-body">{!! app(\App\Support\ContentHtml::class)->clean($section['body'] ?? '') !!}</div>
                            @if(!empty($section['button_url']) && !empty($section['button_label']))<a class="case-button" href="{{ $section['button_url'] }}">{{ $section['button_label'] }}</a>@endif
                        </div>
                        <div class="case-visual">
                            @if($image)@if(!empty($section['image_url']))<a href="{{ $section['image_url'] }}">@endif<img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" width="{{ $image['width'] }}" height="{{ $image['height'] }}" loading="{{ $section['type'] === 'hero' ? 'eager' : 'lazy' }}" @if($section['type'] === 'hero') fetchpriority="high" @endif>@if(!empty($section['image_url']))</a>@endif @endif
                            @if(!empty($section['badge_value']))<div class="case-badge"><strong>{{ $section['badge_value'] }}</strong><span>{{ $section['badge_label'] }}</span></div>@endif
                        </div>
                    </div>
                @else
                    <div class="case-heading">
                        @if(!empty($section['eyebrow']) && $section['type'] !== 'intro')<p class="case-eyebrow">{{ $section['eyebrow'] }}</p>@endif
                        @if($section['type'] === 'intro')<h1>{{ $section['title'] }}</h1>@if(!empty($section['eyebrow']))<p class="case-eyebrow">{{ $section['eyebrow'] }}</p>@endif @elseif($section['type'] !== 'columns')<h2>{{ $section['title'] }}</h2>@endif
                        <div class="case-body">{!! app(\App\Support\ContentHtml::class)->clean($section['body'] ?? '') !!}</div>
                    </div>
                    @if(in_array($section['type'], ['results', 'impact']))
                        <div class="case-stats">@foreach($section['cards'] as $card)<div class="case-stat"><strong>{{ $card['title'] }}</strong><span>{{ $card['label'] }}</span></div>@endforeach</div>
                    @elseif(in_array($section['type'], ['services', 'gallery', 'columns']))
                        <div class="case-feature-grid">
                            @foreach($section['cards'] as $card)
                                @php($cardImage = $images[$card['image_id'] ?? 0] ?? null)
                                <article class="case-feature-card">
                                    @if($cardImage)<img src="{{ $cardImage['url'] }}" alt="{{ $cardImage['alt'] }}" width="{{ $cardImage['width'] }}" height="{{ $cardImage['height'] }}" loading="lazy">@endif
                                    <div class="case-feature-copy">
                                        @if(!empty($card['icon']))<span class="case-feature-icon" aria-hidden="true">{{ $card['icon'] }}</span>@endif
                                        <h3>@if(!empty($card['url']))<a href="{{ $card['url'] }}">{{ $card['title'] }}</a>@else{{ $card['title'] }}@endif</h3>
                                        <div class="case-body">{!! app(\App\Support\ContentHtml::class)->clean($card['body'] ?? '') !!}</div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif($section['type'] === 'testimonial')
                        <p class="case-attribution">{{ $section['badge_label'] ?? '' }}</p>
                    @elseif($section['type'] === 'related')
                        <div class="case-carousel" data-case-carousel>
                            <button type="button" class="case-carousel__previous" aria-label="Previous projects">‹</button>
                            <div class="case-cards" tabindex="0" aria-label="Related portfolio projects">
                                @foreach($section['cards'] as $card)
                                    @php($cardImage = $images[$card['image_id'] ?? 0] ?? null)
                                    <a class="case-card" href="{{ $card['url'] ?: route('portfolio') }}">@if($cardImage)<img src="{{ $cardImage['url'] }}" alt="{{ $cardImage['alt'] }}" width="770" height="550" loading="lazy">@endif<div><p>{{ $card['label'] }}</p><h3>{{ $card['title'] }}</h3></div></a>
                                @endforeach
                            </div>
                            <button type="button" class="case-carousel__next" aria-label="Next projects">›</button>
                            <div class="case-carousel__dots" aria-label="Choose a project">
                                @foreach($section['cards'] as $card)<button type="button" data-case-slide="{{ $loop->index }}" aria-label="Go to project {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>@endforeach
                            </div>
                        </div>
                    @endif
                    @if(!empty($section['button_url']) && !empty($section['button_label']))<div class="case-action"><a class="case-button" href="{{ $section['button_url'] }}">{{ $section['button_label'] }}</a></div>@endif
                @endif
            </div>
        </section>
    @endforeach
</article>
@endsection
