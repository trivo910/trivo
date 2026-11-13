@extends('layouts.public')

@section('title', $item['title'] . ' | Trivo')
@section('description', $item['summary'])

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Case study</p>
        <h1 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">{{ $item['title'] }}</h1>
        <div class="mt-10 h-64 rounded-[28px] bg-[linear-gradient(135deg,#006ace_0%,#5bb0ff_35%,#ffbe11_100%)]"></div>

        <div class="prose prose-slate mt-10 max-w-none text-lg leading-8 text-slate-700">
            {!! $item['content'] !!}
        </div>

        <div class="mt-12">
            <a href="{{ route('portfolio') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-800 transition hover:border-primary hover:text-primary">Back to portfolio</a>
        </div>
    </section>
@endsection
