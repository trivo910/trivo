@extends('layouts.public')

@section('title', 'About Trivo | Digital Marketing Agency in India')
@section('description', 'Learn how Trivo helps ambitious brands grow with performance marketing, content strategy, design, and conversion-focused digital systems.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">About us</p>
                <h1 class="mt-4 text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl">We build momentum for brands that want to grow faster.</h1>
            </div>
            <div class="rounded-[32px] border border-slate-200 bg-[#f7f7fa] p-8">
                <p class="text-lg leading-8 text-slate-700">Trivo partners with startups, travel brands, and service businesses to create measurable digital growth systems. Our work blends strategic clarity, creative execution, and disciplined optimisation to turn attention into pipeline.</p>
            </div>
        </div>
    </section>

    <section class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                @php
                    $pillars = [
                        ['title' => 'Strategy-first', 'copy' => 'We start with the business objective, audience trends, and the commercial levers that matter most.'],
                        ['title' => 'Creative with accountability', 'copy' => 'Every content, ad, and landing page is designed to improve visibility, trust, and conversion.'],
                        ['title' => 'Measurement built in', 'copy' => 'We optimise around lead quality, ROAS, and pipeline outcomes instead of vanity metrics alone.'],
                    ];
                @endphp

                @foreach ($pillars as $pillar)
                    <article class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#e9e5ff] text-lg font-black text-primary">✓</div>
                        <h2 class="text-2xl font-bold tracking-[-0.06em] text-slate-900">{{ $pillar['title'] }}</h2>
                        <p class="mt-4 text-base leading-7 text-slate-600">{{ $pillar['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Why teams choose us</p>
            <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">We combine channel expertise with sharp commercial thinking.</h2>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Brands scaled', 'value' => '120+'],
                    ['label' => 'Avg. ROI uplift', 'value' => '300%'],
                    ['label' => 'Response speed', 'value' => '24h'],
                    ['label' => 'Retention', 'value' => '92%'],
                ];
            @endphp

            @foreach ($stats as $stat)
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 text-center">
                    <div class="text-4xl font-black tracking-[-0.06em] text-slate-900">{{ $stat['value'] }}</div>
                    <div class="mt-3 text-sm uppercase tracking-[0.14em] text-slate-500">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-slate-900 py-20 text-white">
        <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-secondary">Let’s build your next growth chapter</p>
            <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-white sm:text-5xl">Growth is not a campaign. It’s a system.</h2>
            <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-[#004b8a]">Book a consultation</a>
        </div>
    </section>
@endsection
