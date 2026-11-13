@extends('layouts.public')

@section('title', 'Page not found | Trivo')
@section('description', 'The page you are looking for could not be found.')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="rounded-[32px] border border-slate-200 bg-[#f7f7fa] p-10 text-center shadow-[0_18px_45px_rgba(15,23,42,0.04)]">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">404</p>
            <h1 class="mt-4 text-5xl font-black tracking-[-0.06em] text-slate-900 sm:text-6xl">Page not found</h1>
            <p class="mt-5 text-lg leading-8 text-slate-600">The page you’re looking for may have moved or no longer exists.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-[#004b8a]">Go home</a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-800 transition hover:border-primary hover:text-primary">Contact us</a>
            </div>
        </div>
    </section>
@endsection
