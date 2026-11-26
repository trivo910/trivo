@extends('layouts.public')

@section('title', 'Digital Marketing Services Company India | Trivo')
@section('description', 'Trivo is a leading digital marketing services company in India offering SEO, PPC, social media, and performance marketing tailored for scalable growth.')

@section('content')
    <div class="imported-home">
        <div class="imported-home__content">
            <section class="home-hero">
                <div class="home-hero__orbit" aria-hidden="true">
                    <span class="home-hero__orbit-ring home-hero__orbit-ring--one">
                        <span class="home-hero__orbit-sphere home-hero__orbit-sphere--large"></span>
                    </span>
                    <span class="home-hero__orbit-ring home-hero__orbit-ring--two">
                        <span class="home-hero__orbit-sphere home-hero__orbit-sphere--medium"></span>
                    </span>
                    <span class="home-hero__orbit-ring home-hero__orbit-ring--three">
                        <span class="home-hero__orbit-sphere home-hero__orbit-sphere--small"></span>
                    </span>
                    <span class="home-hero__orbit-glow"></span>
                </div>
                <div class="home-hero__inner">
                    <div class="home-hero__copy">
                        <p class="home-hero__eyebrow"><span class="home-hero__eyebrow-icon" aria-hidden="true">✦</span> AI-DRIVEN GROWTH SOLUTIONS</p>
                        <h1><span class="home-hero__dark-title"><span class="home-hero__word home-hero__word--first">Digital</span> <span class="home-hero__word home-hero__word--second">marketing</span></span><br><span class="home-hero__light-title">that drives<br>revenue.</span></h1>
                        <p>It's time for your brand to shine as we put the spotlight on it. We help attract the attention and generate the revenue your business deserves in the digital world.</p>
                        <div class="home-hero__actions" aria-label="Explore Trivo services">
                            <a class="home-hero__cta home-hero__cta--primary" href="#home-audit" aria-label="Get a free growth audit">
                                <span>Get a free growth audit</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M5 12h13M12 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                            <a class="home-hero__cta home-hero__cta--secondary" href="{{ route('portfolio') }}" aria-label="View our work">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M8 5.8c0-.8.9-1.3 1.6-.9l9.1 5.2c.7.4.7 1.4 0 1.8l-9.1 5.2c-.7.4-1.6-.1-1.6-.9V5.8Z" fill="currentColor"></path>
                                </svg>
                                <span>View our work</span>
                            </a>
                        </div>
                        <ul class="home-hero__proof" aria-label="How we help your business grow">
                            <li><span aria-hidden="true">✓</span>Data-Driven Strategy</li>
                            <li><span aria-hidden="true">✓</span>Performance Marketing</li>
                            <li><span aria-hidden="true">✓</span>Measurable Growth</li>
                        </ul>
                    </div>
                    <div class="home-hero__visual" role="group" aria-label="Marketing campaign performance preview">
                        <article class="home-hero-card home-hero-card--budget" aria-label="Campaign budget: 42,600 rupees spent from a 55,000 rupee budget">
                            <div class="home-budget-insight" aria-label="Conversion rate increased 38.4 percent">
                                <span class="home-budget-insight__icon" aria-hidden="true">
                                    <svg viewBox="0 0 48 48" focusable="false">
                                        <path d="M8 33 19 22l8 8L40 15M29 15h11v11" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                <span class="home-budget-insight__copy"><span>Conversion rate</span><strong>+38.4%</strong></span>
                            </div>
                            <div class="home-hero-card__budget-total">₹<span data-count-start="55000" data-count-target="42600" data-count-format="grouped">42,600</span></div>
                            <p class="home-hero-card__budget-caption">Budget <strong>55,000</strong></p>
                            <svg class="home-budget-chart" viewBox="0 0 300 180" role="img" aria-label="Live campaign budget trend">
                                <path class="home-budget-chart__baseline" d="M12 154H288"></path>
                                <path class="home-budget-chart__trend" id="home-budget-trend" d="M14 112 C28 110 28 145 44 143 S62 92 79 100 S94 151 112 140 S130 74 147 88 S164 153 181 145 S197 55 214 58 S233 105 251 99 S273 93 286 70"></path>
                                <circle class="home-budget-chart__dot" r="5">
                                    <animateMotion begin="indefinite" dur="5s" repeatCount="indefinite" rotate="auto">
                                        <mpath href="#home-budget-trend"></mpath>
                                    </animateMotion>
                                </circle>
                            </svg>
                            <div class="home-hero-card__legend"><span><i></i>Feb 2025</span><span><i></i>Jan 2025</span></div>
                        </article>

                        <article class="home-hero-card home-hero-card--campaign" aria-label="Facebook Marketing Campaign: 690 clicks, goal 1,000">
                            <h2>Facebook Marketing<br>Campaign</h2>
                            <div class="home-campaign-ring">
                                <svg viewBox="0 0 160 160" role="img" aria-label="Campaign progress toward 1,000 clicks">
                                    <circle class="home-campaign-ring__track" cx="80" cy="80" r="61"></circle>
                                    <circle class="home-campaign-ring__progress" cx="80" cy="80" r="61"></circle>
                                </svg>
                                <div class="home-campaign-ring__value"><strong data-count-start="0" data-count-target="690" data-count-mode="fast-then-slow">690</strong><span>Goal: 1000</span></div>
                            </div>
                            <div class="home-hero-card__legend"><span><i></i>Clicks: <strong data-count-start="0" data-count-target="690" data-count-mode="fast-then-slow">690</strong></span><span><i></i>Goal: <strong>1000</strong></span></div>
                        </article>

                        <article class="home-hero-card home-hero-card--leads" aria-label="Generated traffic and leads, average annual growth rate 61 percent">
                            <h2>Generated Traffic<br>&amp; Leads</h2>
                            <svg class="home-leads-chart" viewBox="0 0 240 148" role="img" aria-label="Animated traffic and leads bar chart">
                                <path class="home-leads-chart__baseline" d="M8 132H232"></path>
                                <g class="home-leads-chart__bars">
                                    <rect x="22" y="62" width="12" height="70" rx="6"></rect><rect x="48" y="38" width="12" height="94" rx="6"></rect><rect x="74" y="70" width="12" height="62" rx="6"></rect><rect x="100" y="54" width="12" height="78" rx="6"></rect><rect x="126" y="76" width="12" height="56" rx="6"></rect><rect x="152" y="46" width="12" height="86" rx="6"></rect><rect x="178" y="24" width="12" height="108" rx="6"></rect><rect x="204" y="36" width="12" height="96" rx="6"></rect>
                                </g>
                            </svg>
                            <div class="home-leads-stat"><strong><span data-count-start="0" data-count-target="61">61</span>%</strong><span>Average annual growth rate</span></div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="home-partners" aria-label="Our partners">
                <p>A partner, not a vendor</p>
                <div class="home-partners__marquee" aria-label="Partner logos">
                    <div class="home-partners__track">
                        <div class="home-partners__group">
                            <img src="{{ asset('wp-content/uploads/2023/05/semrush-partner-transparent.webp') }}" alt="Semrush Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/hostinger-partner-transparent.webp') }}" alt="Hostinger Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/google-my-business-partner-transparent.webp') }}" alt="Google My Business Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/shopify-partner-transparent.webp') }}" alt="Shopify Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/meta-business-partner-transparent.webp') }}" alt="Meta Business Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/cashfree-payments-logo-transparent.webp') }}" alt="Cashfree Payments Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/google-cloud-partner-transparent.webp') }}" alt="Google Cloud Partner" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/razorpay-logo-transparent.webp') }}" alt="Razorpay Partner" width="142" height="40">
                        </div>
                        <div class="home-partners__group" aria-hidden="true">
                            <img src="{{ asset('wp-content/uploads/2023/05/semrush-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/hostinger-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/google-my-business-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/shopify-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/meta-business-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/cashfree-payments-logo-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/google-cloud-partner-transparent.webp') }}" alt="" width="142" height="40">
                            <img src="{{ asset('wp-content/uploads/2023/05/razorpay-logo-transparent.webp') }}" alt="" width="142" height="40">
                        </div>
                    </div>
                </div>
            </section>

            <div class="home-sections">
                <section class="home-section home-section--services">
                    <h2>What we do</h2>
                    <div class="home-section__group">
                        <h3>Smarter Solutions,<br>Better Markets</h3>
                        <p>Trivo IT Solution is a tech-enabled travel marketing solutions provider, specializing in creating custom strategies for travel agencies, tour operators, and hospitality businesses.</p>
                    </div>

                    @php
                        $services = [
                            [
                                'title' => 'Search engine optimization',
                                'copy' => 'Increase your organic traffic by ranking high on search engines and AI discovery tools like ChatGPT.',
                                'benefits' => ['Keyword research and content strategy', 'On-page & technical SEO optimization', 'High-quality link building', 'Performance tracking and reporting', 'Continuous improvement'],
                                'stat' => '93%',
                                'stat_copy' => 'Of customers achieved a higher search visibility within 6 months.',
                                'url' => '/search-engine-optimization',
                            ],
                            [
                                'title' => 'Paid search marketing',
                                'copy' => 'Performance marketing helps businesses focus their investment on measurable outcomes like website visits, sign-ups, and sales.',
                                'benefits' => ['Flexible campaigns tailored to your goals', 'Real-time tracking and optimization', 'Transparent, data-backed results', 'Cost-effective for growing businesses', 'Boosts brand visibility and lead generation'],
                                'stat' => '68%',
                                'stat_copy' => 'Performance ads drive better leads by targeting users ready to convert.',
                                'url' => '/paid-search-marketing',
                            ],
                            [
                                'title' => 'Conversion rate optimization',
                                'copy' => 'Unlock your website’s full potential and boost your revenue with conversion rate optimization.',
                                'benefits' => ['Improve contact form completions', 'Increase online orders', 'Decrease abandoned shopping cart rates', 'Accelerate website-generated revenue', 'Maximize site design and conversion rates'],
                                'stat' => '71%',
                                'stat_copy' => 'Of customers reported that contact form submissions increased.',
                                'url' => '/rate-optimization',
                            ],
                            [
                                'title' => 'Social media marketing',
                                'copy' => 'Finding the balance between staying current and relying on proven strategies is essential in the ever-changing landscape of marketing.',
                                'benefits' => ['Social media design', 'Social media management', 'Custom ad strategy', 'Unique ad campaigns and ads', 'Advanced demographic targeting'],
                                'stat' => '86%',
                                'stat_copy' => 'Of customers reported an increase in conversion rates through social media marketing.',
                                'url' => '/social-media-marketing',
                            ],
                            [
                                'title' => 'Performance marketing',
                                'copy' => 'Invest smartly in digital efforts by focusing on trackable outcomes that matter, like website visits, sign-ups, or sales.',
                                'benefits' => ['Flexible campaigns tailored to your goals', 'Real-time tracking and optimization', 'Transparent, data-backed results', 'Cost-effective for growing businesses', 'Boosts brand visibility and lead generation'],
                                'stat' => '68%',
                                'stat_copy' => 'Performance ads drive better leads by targeting users ready to convert.',
                                'url' => '/performance-marketing-agency-nagpur',
                            ],
                        ];
                    @endphp

                    <div class="home-service-tabs">
                        <div class="home-service-tabs__list" role="tablist" aria-label="Marketing services">
                            @foreach ($services as $index => $service)
                                <button
                                    class="home-service-tabs__tab"
                                    type="button"
                                    role="tab"
                                    id="home-service-tab-{{ $index }}"
                                    aria-controls="home-service-panel-{{ $index }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    tabindex="{{ $index === 0 ? '0' : '-1' }}"
                                ><span class="home-service-tabs__tab-label">{{ $service['title'] }}</span></button>
                            @endforeach
                        </div>

                        @foreach ($services as $index => $service)
                            <section
                                class="home-service-tabs__panel"
                                id="home-service-panel-{{ $index }}"
                                role="tabpanel"
                                aria-labelledby="home-service-tab-{{ $index }}"
                                @if ($index > 0) hidden @endif
                            >
                                <div class="home-service-tabs__copy">
                                    <p>{{ $service['copy'] }}</p>
                                    <ul>
                                        @foreach ($service['benefits'] as $benefit)
                                            <li>{{ $benefit }}</li>
                                        @endforeach
                                    </ul>
                                    <a href="{{ url($service['url']) }}">Learn more</a>
                                </div>
                                <div class="home-service-tabs__statistics">
                                    <section>
                                        <h3>{{ $service['stat'] }}</h3>
                                        <p>{{ $service['stat_copy'] }}</p>
                                    </section>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </section>

                <section class="trivo-about" aria-labelledby="home-about-title">
                    <h2 id="home-about-title" class="trivo-about__label">About Trivo</h2>
                    <div class="trivo-about__intro">
                        <h3 class="trivo-about__title">Customer Data,<br><span>Thoughtfully Built</span></h3>
                        <div class="trivo-about__copy">
                            <p>Trivo IT Solution is a tech-enabled travel marketing solutions provider, specializing in creating custom strategies for travel agencies, tour operators, and hospitality businesses based on their unique needs and goals. Our result-driven approach has helped travel businesses generate over &#8377;3.5 lakh in sales and attract over 1.8 million potential leads, boosting their bookings, visibility, and revenue growth.</p>
                            <a href="{{ route('about') }}">More about our unique approach <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                    <ul class="trivo-about__milestones">
                        <li>
                            <span class="trivo-about__milestone-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M14.5 5.5c1.8-1.8 4.5-2 6-2 .1 1.5-.2 4.2-2 6l-6.8 6.8-4-4 6.8-6.8Z"/><path d="m7.7 12.3-3.4.4-2 3.5 5 .5m4.4.1-.4 3.4-3.5 2-.5-5m6.7-8.8h.1M5.5 18.5l-2 2"/></svg></span>
                            <span class="trivo-about__milestone-copy">Our customers have raised over $35mn in funding</span>
                        </li>
                        <li>
                            <span class="trivo-about__milestone-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 6H5v2a4 4 0 0 0 4 4m7-6h3v2a4 4 0 0 1-4 4m-3 1v4m-4 3h8m-7-3h6l2 3H7l2-3Z"/></svg></span>
                            <span class="trivo-about__milestone-copy">In over 10 years, we’ve never lost a customer</span>
                        </li>
                        <li>
                            <span class="trivo-about__milestone-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg></span>
                            <span class="trivo-about__milestone-copy">We work with the world’s fastest-growing startups</span>
                        </li>
                        <li>
                            <span class="trivo-about__milestone-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M16 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20m7-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7.7a4 4 0 0 1 0 7.4m1 4.1a4 4 0 0 1 3 3.9V20"/></svg></span>
                            <span class="trivo-about__milestone-copy">Distributed team of over 20 marketers, podcasters, and copywriters</span>
                        </li>
                    </ul>
                </section>

                <section class="home-showcase">
                    <div class="home-section__group home-section__group--customer-overline">
                        <h6>Our Customers Are Some Of The World’s...</h6>
                    </div>
                    <div class="home-section__group home-section__group--fastest">
                        <h3>Fastest growing companies</h3>
                        @if(count($caseStudies) > 0)
                            <div class="home-case-study-carousel" role="region" aria-label="Featured client case studies">
                                <button class="home-case-study-carousel__arrow home-case-study-carousel__arrow--previous" type="button" aria-label="Previous case studies" data-carousel-direction="-1">‹</button>
                                <div class="home-case-studies-viewport">
                                    <div class="home-case-studies" id="home-case-studies-track" role="list">
                                        @foreach($caseStudies as $caseStudy)
                                            <article class="home-case-study" role="listitem">
                                                <a class="home-case-study__link" href="{{ url($caseStudy['path']) }}">
                                                    <img src="{{ $caseStudy['image'] }}" alt="{{ $caseStudy['title'] }}" loading="lazy" decoding="async">
                                                </a>
                                                <p class="home-case-study__date">{{ $caseStudy['date'] }}</p>
                                                <h4><a href="{{ url($caseStudy['path']) }}">{{ $caseStudy['title'] }}</a></h4>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                                <button class="home-case-study-carousel__arrow home-case-study-carousel__arrow--next" type="button" aria-label="Next case studies" data-carousel-direction="1">›</button>
                                <div class="home-case-study-pagination" role="group" aria-label="Choose a case study slide">
                                    <button class="home-case-study-pagination__button" type="button" aria-label="Go to case study slide 1" aria-controls="home-case-studies-track" data-slide-index="0" aria-current="true"></button>
                                </div>
                            </div>
                        @endif
                        <a href="{{ route('portfolio') }}">View all</a>
                    </div>
                </section>
                @include('partials.home-enquiry', ['variant' => 'audit'])

                <section class="home-section home-section--results home-results--premium" aria-labelledby="home-results-title">
                    <h2 id="home-results-title">Driving Digital Success With <span>Proven Results</span></h2>
                    <div class="home-section__group home-section__group--results-intro">
                        <h6>We deliver reliable technology solutions that help businesses grow, scale, and succeed in the digital world.</h6>
                        <div class="home-results__stats">
                            <article class="home-results__stat" style="--result-accent: #006ace; --result-tint: #e1efff; --result-shadow: rgb(0 106 206 / 24%); --result-delay: 0ms;">
                                <span class="home-results__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 8 4.5-8 4.5-8-4.5L12 2Z"/><path d="m4 11 8 4.5 8-4.5"/><path d="m4 15.5 8 4.5 8-4.5"/></svg></span>
                                <strong><span data-results-counter data-results-target="1000" data-results-suffix="+">1,000+</span></strong>
                                <span>Projects Successfully Delivered</span>
                            </article>
                            <article class="home-results__stat" style="--result-accent: #8738e8; --result-tint: #f0e5ff; --result-shadow: rgb(135 56 232 / 24%); --result-delay: 100ms;">
                                <span class="home-results__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                                <strong><span data-results-counter data-results-target="300" data-results-suffix="+">300+</span></strong>
                                <span>Happy Clients Worldwide</span>
                            </article>
                            <article class="home-results__stat" style="--result-accent: #d99a00; --result-tint: #fff3cf; --result-shadow: rgb(217 154 0 / 24%); --result-delay: 200ms;">
                                <span class="home-results__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M8 2v5M16 2v5M3 9.5h18M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01"/></svg></span>
                                <strong><span data-results-counter data-results-target="15" data-results-suffix="+">15+</span></strong>
                                <span>Years of Industry Experience</span>
                            </article>
                            <article class="home-results__stat" style="--result-accent: #28a767; --result-tint: #dff5e9; --result-shadow: rgb(40 167 103 / 24%); --result-delay: 300ms;">
                                <span class="home-results__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 5 2.9v5.8L12 14l-5-2.8V5.4l5-2.9Z"/><path d="m7 5.4 5 2.8 5-2.8M12 8.2V14"/><path d="m7 12 5 2.8 5-2.8v5.7l-5 2.8-5-2.8V12Z"/><path d="m7 17.7 5 2.8 5-2.8"/></svg></span>
                                <strong><span data-results-counter data-results-target="100" data-results-suffix="+">100+</span></strong>
                                <span>Technologies &amp; Tools Expertise</span>
                            </article>
                        </div>
                    </div>
                    <div class="home-section__group home-section__group--results-testimonials">
                        <h3>Driving digital revenue for our 1000+ satisfied customers</h3>
                        <div class="home-results__proof">
                            <div class="home-results__metric">
                                <strong>21%</strong>
                                <span>Average Traffic Increase for Clients</span>
                            </div>
                            <div class="home-results__testimonials">
                                <blockquote class="home-results__testimonial">
                                    <p>Working with Trivo IT Solution transformed our business! Their tailored strategy boosted our online presence, SEO, and social media engagement. We saw a significant rise in website traffic and lead generation within months. Highly professional and detail-oriented team!</p>
                                    <footer><cite>Parwez Alam</cite></footer>
                                </blockquote>
                                <blockquote class="home-results__testimonial">
                                    <p>We are happy with Trivo IT Solution. They are doing a great job for us far beyond our expectations. We contracted them with a goal to become an online supplier for travel agencies and they fulfilled it very nicely.</p>
                                    <footer><cite>Muhammad Imran</cite><span>CEO &amp; Founder</span></footer>
                                </blockquote>
                                <blockquote class="home-results__testimonial">
                                    <p>I am very grateful to the Trivo IT Solution team and mostly to Mr. Kashif Khan. He helped us select the proper product as per our needs. Proud to say my portal was developed and designed by Trivo IT Solution.</p>
                                    <footer><cite>Ronak Patel</cite><span>Director, United Air Travel &amp; Tours London UK</span></footer>
                                </blockquote>
                                <blockquote class="home-results__testimonial">
                                    <p>I had a fantastic experience with Trivo. Their team was professional, creative, and responsive, tailoring a marketing strategy that boosted our engagement and sales. They provided clear communication and valuable insights throughout the process. Highly recommend them for any business looking to enhance their marketing efforts!</p>
                                    <footer><cite>Mustak Panwar</cite></footer>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                    <img class="home-results__visual" src="{{ asset('wp-content/uploads/2025/03/Untitled-design.png') }}" alt="" width="480" height="520" loading="lazy">
                </section>

                <section class="home-section home-section--team">
                    <h2>The team behind</h2>
                    <div class="home-section__group home-section__group--team">
                        <h3>Powering Your Growth with the Expertise of Our Visionary Team</h3>
                        <p>At Trivo, we focus on fostering long-term relationships with our clients. Leveraging our extensive industry knowledge, we provide essential insights to enhance your brand, drive business success, and reduce risks, paving the way for continuous growth.</p>
                        <a href="{{ url('/meet-the-team') }}">Meet the team</a>
                        <img src="{{ asset('wp-content/uploads/2025/03/Untitled-313-x-375-px.png') }}" alt="The Trivo team" width="313" height="375" loading="lazy">
                    </div>
                </section>

                <section class="home-section home-section--blog">
                    <h2>Blog</h2>
                    <div class="home-section__group">
                        <h3>Digital marketing &amp; industry insights</h3>
                        <a href="{{ route('blog') }}">View more</a>
                    </div>
                    <div class="home-blog-carousel" data-home-blog-carousel>
                        <div class="home-blog-cards" id="home-blog-track" tabindex="0" aria-label="Latest blog posts">
                            @forelse($homeBlogPosts as $post)
                                @php($image = $homeBlogMedia->get($post->featured_media_id))
                                <a class="home-blog-card" href="{{ url($post->path) }}" aria-label="Read more: {{ $post->title }}">
                                    @if($image && ($image->processing_status === 'ready' || $image->isLegacy()))
                                        <img src="{{ $image->isLegacy() ? asset(ltrim($image->path, '/')) : Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt ?: $post->title }}" loading="lazy">
                                    @elseif(preg_match('~<img[^>]+src=[\x22\x27](/wp-content/uploads/[^\x22\x27]+)~i', $post->body ?? '', $match))
                                        <img src="{{ asset(ltrim($match[1], '/')) }}" alt="{{ $post->title }}" loading="lazy">
                                    @endif
                                    <h3>{{ $post->title }}</h3>
                                    <span class="home-blog-card__read-more" aria-hidden="true">→</span>
                                </a>
                            @empty
                                <a class="home-blog-card" href="{{ url('/how-a-digital-marketing-company-in-mumbai-can-transform-your-business') }}" aria-label="Read more: How a digital marketing company in Mumbai can transform your business">
                                    <img src="{{ asset('wp-content/uploads/2026/10/Digital-Marketing-Growth-Launch.png') }}" alt="Digital marketing growth strategy" loading="lazy">
                                    <h3>How a digital marketing company in Mumbai can transform your business</h3>
                                    <span class="home-blog-card__read-more" aria-hidden="true">→</span>
                                </a>
                                <a class="home-blog-card" href="{{ url('/discover-the-role-of-an-seo-company-in-surat-for-the-travel-industry') }}" aria-label="Read more: Discover the role of an SEO company in Surat for the travel industry">
                                    <img src="{{ asset('wp-content/uploads/2026/09/ChatGPT-Image-Sep-29-2026-07_30_44-PM-1.png') }}" alt="SEO for travel businesses" loading="lazy">
                                    <h3>Discover the role of an SEO company in Surat for the travel industry</h3>
                                    <span class="home-blog-card__read-more" aria-hidden="true">→</span>
                                </a>
                            @endforelse
                        </div>
                        <div class="home-blog-carousel__controls" aria-label="Blog slider controls">
                            <button type="button" data-home-blog-prev aria-label="Previous blog posts" aria-controls="home-blog-track" disabled>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5m7 7-7-7 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </button>
                            <button type="button" data-home-blog-next aria-label="Next blog posts" aria-controls="home-blog-track">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14m-7-7 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </button>
                        </div>
                    </div>
                </section>

                @include('partials.home-clients', ['headingId' => 'home-clients-title'])
            </div>
        </div>
    </div>
@endsection
