@extends('layouts.public')

@section('title', 'Contact Trivo | Digital Marketing Consultant in India')
@section('description', 'Speak with Trivo about SEO, PPC, social media growth, website strategy, and performance marketing for your business.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Contact</p>
                <h1 class="mt-4 text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl">Let's talk about your next growth move.</h1>
                <div class="mt-8 space-y-5 text-slate-600">
                    <p><strong class="text-slate-900">Email:</strong> <a href="mailto:letstalk@trivo.in" class="text-primary hover:text-[#004b8a]">letstalk@trivo.in</a></p>
                    <p><strong class="text-slate-900">Phone:</strong> <a href="tel:+919999999999" class="text-primary hover:text-[#004b8a]">+91 99999 99999</a></p>
                    <p><strong class="text-slate-900">Location:</strong> India</p>
                </div>
            </div>

            <div class="rounded-[32px] border border-slate-200 bg-[#f7f7fa] p-6 sm:p-8">
                <form action="{{ route('thank-you') }}" method="get" class="space-y-5">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Name</label>
                        <input id="name" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="Your name" required>
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input id="email" type="email" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="you@example.com" required>
                    </div>

                    <div>
                        <label for="company" class="mb-2 block text-sm font-semibold text-slate-700">Company</label>
                        <input id="company" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="Your company">
                    </div>

                    <div>
                        <label for="message" class="mb-2 block text-sm font-semibold text-slate-700">Project goals</label>
                        <textarea id="message" rows="5" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="Tell us about your business and goals" required></textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-[#004b8a]">Send enquiry</button>
                </form>
            </div>
        </div>
    </section>
@endsection
