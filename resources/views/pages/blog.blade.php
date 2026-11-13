@extends('layouts.public')

@section('title', 'Trivo Blog | Digital Marketing Insights')
@section('description', 'Explore digital marketing insights, SEO strategy, paid media growth, and conversion-focused ideas from the Trivo team.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Insights</p>
            <h1 class="mt-4 text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl">Fresh thinking for smarter growth.</h1>
            <p class="mt-6 text-lg leading-8 text-slate-600">Actionable marketing ideas for brands that want better visibility, stronger conversion, and more resilient pipeline performance.</p>
        </div>
    </section>

    <section class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.04)]">
                        <div class="h-44 bg-[linear-gradient(135deg,#006ace_0%,#5bb0ff_35%,#ffbe11_100%)]"></div>
                        <div class="p-6">
                            <span class="inline-flex rounded-full bg-[#e9e5ff] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">{{ $post['category'] }}</span>
                            <h2 class="mt-4 text-2xl font-bold tracking-[-0.06em] text-slate-900">{{ $post['title'] }}</h2>
                            <p class="mt-3 text-sm text-slate-500">{{ $post['date'] }}</p>
                            <p class="mt-4 text-base leading-7 text-slate-600">{{ $post['excerpt'] }}</p>
                            <a href="{{ $post['url'] ?? route('blog.post', ['slug' => $post['slug']]) }}" class="mt-6 inline-flex text-sm font-semibold text-slate-900">Read article <span class="ml-2" aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
