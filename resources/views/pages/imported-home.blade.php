@extends('layouts.public')

@section('title', $record->seo_title ?: $record->title.' | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('robots', $record->robots ?: config('seo.default_robots'))

@section('content')
    <div class="imported-home">
        @unless ($hasHeadingOne)
            <h1 class="sr-only">{{ $record->title }}</h1>
        @endunless
        <div class="imported-home__content">
            {!! $bodyHtml !!}
        </div>
    </div>
@endsection
