@extends('layouts.public')

@section('title', 'Digital Marketing Services | SEO, PPC, Social Media & Web')
@section('description', 'Explore Trivo growth services including SEO, PPC, social media marketing, website strategy and conversion-focused web design.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Our services</p>
            <h1 class="mt-4 text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl">Digital marketing services designed to scale demand.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-600">From search and social to landing page optimisation, we build the systems that move qualified leads from interest to action.</p>
        </div>
    </section>

    <section class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2">
                @php
                    $services = [
                        ['title' => 'SEO strategy', 'copy' => 'Improve visibility, outrank competitors, and turn high-intent traffic into a steady pipeline of qualified leads.', 'meta' => 'Organic growth'],
                        ['title' => 'Paid search', 'copy' => 'Launch and optimise Google Ads and search campaigns that generate faster acquisition and clearer conversion data.', 'meta' => 'PPC'],
                        ['title' => 'Social media marketing', 'copy' => 'Create campaigns that build attention, community trust and revenue from the channels your audience already uses.', 'meta' => 'Brand + performance'],
                        ['title' => 'Web design & CRO', 'copy' => 'Strengthen your website experience with landing pages and UX improvements that reduce friction and increase conversions.', 'meta' => 'Conversion design'],
                    ];
                @endphp

                @foreach ($services as $service)
                    <article class="rounded-[28px] border border-slate-200 bg-white p-7 shadow-[0_18px_45px_rgba(15,23,42,0.04)]">
                        <span class="inline-flex rounded-full bg-[#e9e5ff] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-primary">{{ $service['meta'] }}</span>
                        <h2 class="mt-5 text-3xl font-bold tracking-[-0.06em] text-slate-900">{{ $service['title'] }}</h2>
                        <p class="mt-4 text-base leading-7 text-slate-600">{{ $service['copy'] }}</p>
                        <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-900">Get started <span aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="rounded-[32px] bg-slate-900 p-8 text-white sm:p-12">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-secondary">What we deliver</p>
                    <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-white sm:text-5xl">A partner for strategy, execution, and measurable growth.</h2>
                </div>
                <ul class="space-y-4 text-base text-slate-200">
                    <li>• Channel strategy aligned to revenue goals</li>
                    <li>• High-converting landing page design</li>
                    <li>• Performance reporting and optimisation loops</li>
                    <li>• Creative testing for awareness and acquisition</li>
                </ul>
            </div>
        </div>
    </section>
@endsection
