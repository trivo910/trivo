@extends('layouts.public')

@section('title', 'Digital Marketing Services Company India | Trivo')
@section('description', 'Trivo is a leading digital marketing services company in India offering SEO, PPC, social media and performance marketing tailored for scalable growth.')

@section('content')
    <section class="relative overflow-hidden bg-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(0,106,206,0.14),transparent_30%),radial-gradient(circle_at_right,rgba(255,190,17,0.16),transparent_28%)]"></div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-24">
            <div>
                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-primary">Growth partner for ambitious brands</span>
                <h1 class="mt-6 max-w-xl text-5xl font-black tracking-[-0.07em] text-slate-900 sm:text-6xl lg:text-[66px]">Digital marketing that drives revenue.</h1>
                <p class="mt-6 max-w-xl text-lg text-slate-600">We build performance marketing systems that turn search, social, and content into measurable pipeline and consistent sales.</p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="#contact" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-[#004b8a]">Get a free consultation</a>
                    <a href="#work" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-800 transition hover:border-primary hover:text-primary">See case studies</a>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-8 text-sm text-slate-500">
                    <div>
                        <div class="text-2xl font-black tracking-[-0.06em] text-slate-900">4.8/5</div>
                        <div>Client rating</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black tracking-[-0.06em] text-slate-900">120+</div>
                        <div>Brands scaled</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black tracking-[-0.06em] text-slate-900">300%</div>
                        <div>Avg. ROI uplift</div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-primary/15 blur-3xl"></div>
                <div class="absolute -right-6 bottom-8 h-40 w-40 rounded-full bg-secondary/30 blur-3xl"></div>

                <div class="relative rounded-[32px] border border-slate-200 bg-white p-5 shadow-[0_30px_80px_rgba(15,23,42,0.08)] sm:p-7">
                    <div class="rounded-[28px] bg-[#f7f7fa] p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Campaign overview</p>
                                <h2 class="mt-2 text-3xl font-black tracking-[-0.06em] text-slate-900">Growth engine</h2>
                            </div>
                            <div class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">+72.4%</div>
                        </div>

                        <div class="mt-8 space-y-5">
                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                                <div class="flex items-baseline justify-between text-sm text-slate-500">
                                    <span>Qualified leads</span>
                                    <span class="font-semibold text-slate-900">8.4K</span>
                                </div>
                                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-[82%] rounded-full bg-primary"></div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-primary p-4 text-white shadow-lg shadow-primary/20">
                                    <div class="text-xs uppercase tracking-[0.2em] text-primary-foreground/80">ROAS</div>
                                    <div class="mt-4 text-3xl font-black tracking-[-0.08em]">4.2x</div>
                                </div>
                                <div class="rounded-2xl bg-[#e9e5ff] p-4 text-slate-900">
                                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">CPL</div>
                                    <div class="mt-4 text-3xl font-black tracking-[-0.08em]">₹486</div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between text-sm text-slate-500">
                                    <span>Channel mix</span>
                                    <span class="font-semibold text-slate-900">Best performance</span>
                                </div>
                                <div class="mt-4 flex items-center gap-3">
                                    <div class="flex-1">
                                        <div class="mb-2 h-2.5 rounded-full bg-slate-100">
                                            <div class="h-full w-[64%] rounded-full bg-primary"></div>
                                        </div>
                                        <div class="mb-2 h-2.5 rounded-full bg-slate-100">
                                            <div class="h-full w-[52%] rounded-full bg-[#ffbe11]"></div>
                                        </div>
                                        <div class="h-2.5 rounded-full bg-slate-100">
                                            <div class="h-full w-[77%] rounded-full bg-[#e9e5ff]"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="trivo-about" aria-labelledby="home-about-title">
        <h2 id="home-about-title" class="trivo-about__label">About Trivo</h2>
        <div class="trivo-about__intro">
            <h3 class="trivo-about__title">Customer Data,<br>Thoughtfully Built</h3>
            <div class="trivo-about__copy">
                <p>Trivo IT Solution is a tech-enabled travel marketing solutions provider, specializing in creating custom strategies for travel agencies, tour operators, and hospitality businesses based on their unique needs and goals. Our result-driven approach has helped travel businesses generate over &#8377;3.5 lakh in sales and attract over 1.8 million potential leads, boosting their bookings, visibility, and revenue growth.</p>
                <a href="{{ route('about') }}">More about our unique approach</a>
            </div>
        </div>
        <ul class="trivo-about__milestones">
            <li>Our customers have raised over $35mn in Funding</li>
            <li>In over 10 years, we&#8217;ve never lost a customer</li>
            <li>We works with the world&#8217;s fastest growing startups</li>
            <li>Distributed team of over 20 of the worlds best marketers, podcasters and copywriters</li>
        </ul>
    </section>

    <section class="bg-[#f7f7fa] py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Trusted by high-growth teams and ambitious brands</p>
            <div class="mt-8 grid gap-4 text-center text-sm font-semibold text-slate-400 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-full border border-slate-200 bg-white px-4 py-3">Google</div>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-3">Meta</div>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-3">Shopify</div>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-3">Razorpay</div>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-3">HubSpot</div>
            </div>
        </div>
    </section>

    <section id="services" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">What we do</p>
            <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">Performance marketing built for sustainable growth.</h2>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @php
                $services = [
                    ['title' => 'SEO', 'copy' => 'Rank higher, capture intent, and turn search traffic into consistent demand.', 'accent' => 'bg-primary text-white'],
                    ['title' => 'PPC & Paid Search', 'copy' => 'Scale leads with channel-specific testing and conversion-focused ad creative.', 'accent' => 'bg-[#e9e5ff] text-slate-900'],
                    ['title' => 'Social Media', 'copy' => 'Build brand trust and performance through creative storytelling and audiences.', 'accent' => 'bg-[#fff3cd] text-slate-900'],
                    ['title' => 'Web & CRO', 'copy' => 'Design landing pages and funnels that improve commercial conversion at every step.', 'accent' => 'bg-slate-900 text-white'],
                ];
            @endphp

            @foreach ($services as $service)
                <article class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_14px_45px_rgba(15,23,42,0.04)] transition hover:-translate-y-1 hover:shadow-[0_18px_55px_rgba(15,23,42,0.08)]">
                    <div class="{{ $service['accent'] }} inline-flex rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em]">{{ $service['title'] }}</div>
                    <p class="mt-6 text-base leading-7 text-slate-600">{{ $service['copy'] }}</p>
                    <a href="#contact" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-slate-900">Book a strategy call <span aria-hidden="true">→</span></a>
                </article>
            @endforeach
        </div>
    </section>

    <section id="process" class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Our process</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">A clear strategy from discovery to conversion.</h2>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @php
                    $steps = [
                        ['title' => '01. Diagnose', 'copy' => 'We audit traffic, funnel health, and conversion leaks to find the real growth blockers.'],
                        ['title' => '02. Prioritise', 'copy' => 'We align your goals with the highest-impact channels and content opportunities.'],
                        ['title' => '03. Scale', 'copy' => 'We deploy, optimise, and automate the strategy that keeps performance compounding.'],
                    ];
                @endphp

                @foreach ($steps as $index => $step)
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#e9e5ff] text-lg font-black text-primary">{{ $index + 1 }}</div>
                        <h3 class="text-2xl font-bold tracking-[-0.06em] text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-4 text-base leading-7 text-slate-600">{{ $step['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="work" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Selected work</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">We turn strategy into measurable demand.</h2>
            </div>
            <a href="#contact" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-primary hover:text-primary">Talk to us</a>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @php
                $work = [
                    ['title' => 'Travel brand growth sprint', 'copy' => 'Improved qualified inquiries by 214% through SEO-led landing page optimisation and paid search.', 'tag' => 'SEO + PPC'],
                    ['title' => 'B2B pipeline acceleration', 'copy' => 'Expanded high-intent demand generation across Google Search and LinkedIn remarketing.', 'tag' => 'Performance marketing'],
                    ['title' => 'E-commerce revenue lift', 'copy' => 'Built conversion-focused product journeys and remarketing funnels to improve ROAS.', 'tag' => 'CRO + Social'],
                ];
            @endphp

            @foreach ($work as $item)
                <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_14px_45px_rgba(15,23,42,0.04)]">
                    <div class="h-48 bg-[linear-gradient(135deg,#006ace_0%,#5bb0ff_35%,#ffbe11_100%)]"></div>
                    <div class="p-6">
                        <span class="inline-flex rounded-full bg-[#e9e5ff] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">{{ $item['tag'] }}</span>
                        <h3 class="mt-4 text-2xl font-bold tracking-[-0.06em] text-slate-900">{{ $item['title'] }}</h3>
                        <p class="mt-4 text-base leading-7 text-slate-600">{{ $item['copy'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section id="insights" class="bg-[#f7f7fa] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Insights</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-0.06em] text-slate-900 sm:text-5xl">Practical ideas to sharpen your digital growth strategy.</h2>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @php
                    $posts = [
                        ['title' => 'How to turn intent into pipeline with SEO-led content', 'meta' => 'SEO strategy'],
                        ['title' => 'The paid media dashboard every growth team should track', 'meta' => 'Marketing ops'],
                        ['title' => 'What good conversion design actually looks like', 'meta' => 'CRO'],
                    ];
                @endphp

                @foreach ($posts as $post)
                    <article class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">{{ $post['meta'] }}</span>
                        <h3 class="mt-4 text-2xl font-bold tracking-[-0.06em] text-slate-900">{{ $post['title'] }}</h3>
                        <a href="#contact" class="mt-6 inline-flex text-sm font-semibold text-slate-900">Read more <span class="ml-2" aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[32px] bg-slate-900 p-8 text-white shadow-[0_30px_80px_rgba(15,23,42,0.12)] sm:p-12 lg:p-16">
            <div class="grid gap-10 lg:grid-cols-[1fr_0.9fr]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-secondary">Let’s grow</p>
                    <h2 class="mt-4 max-w-xl text-4xl font-black tracking-[-0.06em] text-white sm:text-5xl">Need a smarter digital growth plan?</h2>
                    <p class="mt-5 max-w-xl text-base leading-7 text-slate-300">Tell us where your pipeline is stalled and we’ll map the next best moves for sales, brand reach, and conversion efficiency.</p>
                </div>

                <form method="POST" action="{{ route('enquiries.store') }}" class="space-y-4 rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    @csrf
                    <input type="hidden" name="form" value="proposal">
                    @include('partials.enquiry-errors', ['formKey' => 'proposal'])
                    @include('partials.enquiry-honeypot')
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-slate-200">Name</label>
                        <input id="name" name="name" maxlength="150" required value="{{ old('name') }}" type="text" class="w-full rounded-2xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-white placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="Your name">
                    </div>
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email</label>
                        <input id="email" name="email" maxlength="254" required value="{{ old('email') }}" type="email" class="w-full rounded-2xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-white placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="hello@brand.com">
                    </div>
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-slate-200">Phone</label>
                        <input id="phone" name="phone" type="tel" maxlength="50" required value="{{ old('phone') }}" class="w-full rounded-2xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-white" autocomplete="tel">
                    </div>
                    <div>
                        <label for="message" class="mb-2 block text-sm font-medium text-slate-200">What do you need?</label>
                        <textarea id="message" name="message" maxlength="10000" rows="4" class="w-full rounded-2xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-white placeholder:text-slate-400 focus:border-primary focus:outline-none" placeholder="Tell us a bit about your goals">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#004b8a]">Send enquiry</button>
                </form>
            </div>
        </div>
    </section>
@endsection
