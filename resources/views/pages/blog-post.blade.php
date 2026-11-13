@extends('layouts.public')

@section('title', $article['title'] . ' | Trivo Blog')
@section('description', strip_tags($article['content']))

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">{{ $article['category'] }}</p>
        <h1 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">{{ $article['title'] }}</h1>
        <p class="mt-4 text-sm font-medium uppercase tracking-[0.12em] text-slate-500">{{ $article['date'] }}</p>

        <div class="mt-10 rounded-[28px] bg-[linear-gradient(135deg,#006ace_0%,#5bb0ff_35%,#ffbe11_100%)] p-12"></div>

        <div class="prose prose-slate mt-10 max-w-none text-lg leading-8 text-slate-700">
            {!! $article['content'] !!}
        </div>
    </article>
@endsection
