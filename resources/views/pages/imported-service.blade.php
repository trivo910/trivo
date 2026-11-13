@extends('layouts.public')

@section('title', $record->seo_title ?: $record->title.' | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('robots', $record->robots ?: config('seo.default_robots'))

@section('content')
    <article class="paid-search-page service-page service-page--{{ trim($record->path, '/') }} {{ in_array($record->path, \App\Services\Content\ServicePageLayout::LOCATION_PATHS, true) ? 'location-page' : '' }}">
        <header class="paid-search-heading"><h1>{{ $record->title }}</h1></header>
        {!! $bodyHtml !!}
    </article>
@endsection
