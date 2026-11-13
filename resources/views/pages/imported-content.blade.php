@extends('layouts.public')

@section('title', $record->seo_title ?: $record->title.' | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('robots', $record->robots ?: config('seo.default_robots'))

@section('content')
    <article class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
        @unless ($hasHeadingOne)
            <h1 class="mb-8 text-center text-4xl font-bold tracking-tight text-slate-950">{{ $record->title }}</h1>
        @endunless
        <div class="public-content">
            {!! $bodyHtml !!}
        </div>
    </article>
@endsection
