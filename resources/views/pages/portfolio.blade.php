@extends('layouts.public')

@section('title', 'Case Studies | Trivo')
@section('description', 'Explore how Trivo helps brands grow through SEO, paid media, content strategy, and conversion-focused campaigns.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Case studies</p>
            <h1 class="mt-4 text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl">Growth stories built around measurable performance.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-600">We translate strategy into outcomes: better visibility, faster lead flow, clearer conversion, and stronger commercial performance.</p>
        </div>
    </section>

    <section class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2">
                @php
                    $work = [
                        ['slug' => 'travel-brand-growth-sprint', 'title' => 'Travel brand growth sprint', 'summary' => 'SEO + paid media strategy for a travel brand targeting higher-intent demand.', 'tag' => 'SEO + PPC'],
                        ['slug' => 'b2b-pipeline-acceleration', 'title' => 'B2B pipeline acceleration', 'summary' => 'More qualified pipeline through better segmentation, creative testing, and conversion tracking.', 'tag' => 'Performance marketing'],
                    ];
                @endphp

                @foreach ($work as $item)
                    <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.04)]">
                        <div class="h-52 bg-[linear-gradient(135deg,#006ace_0%,#5bb0ff_35%,#ffbe11_100%)]"></div>
                        <div class="p-6">
                            <span class="inline-flex rounded-full bg-[#e9e5ff] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">{{ $item['tag'] }}</span>
                            <h2 class="mt-4 text-3xl font-bold tracking-[-0.06em] text-slate-900">{{ $item['title'] }}</h2>
                            <p class="mt-4 text-base leading-7 text-slate-600">{{ $item['summary'] }}</p>
                            <a href="{{ route('portfolio.item', ['slug' => $item['slug']]) }}" class="mt-6 inline-flex text-sm font-semibold text-slate-900">Read case study <span class="ml-2" aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
