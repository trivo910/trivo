@php($usesMarketingLayout = $__env->yieldContent('marketingLayout') === 'true' || request()->routeIs('home', 'portfolio', 'contact', 'team', 'awards', 'career', 'blog', 'blog.post', 'return-refund-policy') || request()->is('about-us') || request()->is('marketing-solutions') || \App\Services\Content\ServicePageLayout::supports(request()->path()))
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $__env->yieldContent('title', 'Trivo | Digital Marketing Company in India') }}</title>
        <meta name="description" content="{{ $__env->yieldContent('description', 'Trivo is a leading digital marketing services company in India offering SEO, PPC, social media and performance marketing solutions.') }}">
        <link rel="canonical" href="{{ $__env->yieldContent('canonical', request()->url()) }}">
        <meta name="robots" content="{{ $__env->yieldContent('robots', config('seo.default_robots')) }}">
        <link rel="preload" href="{{ asset('fonts/montserrat-latin-400.woff2') }}" as="font" type="font/woff2" crossorigin>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-black antialiased">
        <header class="site-header" x-data="{ open: false }">
            <a class="site-header__audit" href="{{ url('/free-audit') }}">
                Get a free audit
                <span aria-hidden="true">→</span>
            </a>
            <div class="site-header__inner">
                <a class="site-header__brand" href="{{ route('home') }}" aria-label="Trivo home">
                    <img src="{{ asset('wp-content/uploads/2025/03/cropped-logo-1-300x118.png') }}" alt="Trivo.in" width="150" height="59">
                </a>

                <nav class="site-header__nav" aria-label="Main navigation">
                    <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home</a>
                    <div class="site-header__solutions">
                        <a class="site-header__solutions-trigger" href="{{ url('/marketing-solutions') }}" @if (request()->is('marketing-solutions')) aria-current="page" @endif>Marketing Solutions</a>
                        <div class="site-header__solutions-menu" aria-label="Marketing solutions">
                            <ul class="site-header__solutions-list">
                                <li><a href="{{ url('/paid-search-marketing') }}"><span aria-hidden="true">⌕</span>Paid Search Marketing</a></li>
                                <li><a href="{{ url('/search-engine-optimization') }}"><span aria-hidden="true">◎</span>Search Engine Optimization</a></li>
                                <li><a href="{{ url('/email-marketing') }}"><span aria-hidden="true">✉</span>Email Marketing</a></li>
                                <li><a href="{{ url('/graphic-design') }}"><span aria-hidden="true">✎</span>Graphic Design</a></li>
                                <li><a href="{{ url('/rate-optimization') }}"><span aria-hidden="true">▥</span>Conversion Rate Optimization</a></li>
                            </ul>
                            <ul class="site-header__solutions-list">
                                <li><a href="{{ url('/social-media-marketing') }}"><span aria-hidden="true">▣</span>Social Media Marketing</a></li>
                                <li><a href="{{ url('/google-shopping') }}"><span aria-hidden="true">g</span>Google Shopping</a></li>
                                <li><a href="{{ url('/performance-marketing-agency-nagpur') }}"><span aria-hidden="true">⌁</span>Performance Marketing</a></li>
                                <li><a href="{{ url('/amazon-shopping') }}"><span aria-hidden="true">a</span>Amazon Shopping</a></li>
                            </ul>
                            <div class="site-header__solutions-feature">
                                <div class="site-header__solutions-art" aria-hidden="true"><span></span></div>
                                <a href="{{ url('/marketing-solutions') }}">Explore all<br>solutions <span>→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="site-header__who">
                        <a class="site-header__who-trigger" href="{{ url('/about-us') }}" @if (request()->is('about-us', 'awards', 'meet-the-team', 'career')) aria-current="page" @endif>Who We Are</a>
                        <ul class="site-header__who-menu" aria-label="Who We Are">
                            <li><a href="{{ url('/about-us') }}" @if (request()->is('about-us')) aria-current="page" @endif>About Us</a></li>
                            <li><a href="{{ url('/awards') }}" @if (request()->is('awards')) aria-current="page" @endif>Awards</a></li>
                            <li><a href="{{ url('/meet-the-team') }}" @if (request()->is('meet-the-team')) aria-current="page" @endif>Meet the Team</a></li>
                            <li><a href="{{ url('/career') }}" @if (request()->is('career')) aria-current="page" @endif>Career – Explore Opportunities</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('portfolio') }}" @if (request()->routeIs('portfolio', 'portfolio.item')) aria-current="page" @endif>Portfolio</a>
                    <a href="{{ route('contact') }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
                </nav>

                <a class="site-header__talk" href="{{ route('contact') }}">
                    Let’s Talk <span aria-hidden="true">→</span>
                </a>

                <button
                    class="site-header__toggle"
                    type="button"
                    aria-label="Toggle navigation"
                    aria-controls="mobile-navigation"
                    :aria-expanded="open.toString()"
                    @click="open = !open"
                >
                    <span></span><span></span><span></span>
                </button>
            </div>
            <nav id="mobile-navigation" class="site-header__mobile-nav" aria-label="Mobile navigation" x-cloak x-show="open" @click.outside="open = false">
                <a href="{{ route('home') }}" @click="open = false">Home</a>
                <a href="{{ url('/marketing-solutions') }}" @click="open = false">Marketing Solutions</a>
                <details class="site-header__mobile-solutions">
                    <summary>Explore services</summary>
                    <a href="{{ url('/paid-search-marketing') }}" @click="open = false">Paid Search Marketing</a>
                    <a href="{{ url('/search-engine-optimization') }}" @click="open = false">Search Engine Optimization</a>
                    <a href="{{ url('/email-marketing') }}" @click="open = false">Email Marketing</a>
                    <a href="{{ url('/graphic-design') }}" @click="open = false">Graphic Design</a>
                    <a href="{{ url('/rate-optimization') }}" @click="open = false">Conversion Rate Optimization</a>
                    <a href="{{ url('/social-media-marketing') }}" @click="open = false">Social Media Marketing</a>
                    <a href="{{ url('/google-shopping') }}" @click="open = false">Google Shopping</a>
                    <a href="{{ url('/performance-marketing-agency-nagpur') }}" @click="open = false">Performance Marketing</a>
                    <a href="{{ url('/amazon-shopping') }}" @click="open = false">Amazon Shopping</a>
                </details>
                <details class="site-header__mobile-who">
                    <summary>Who We Are</summary>
                    <a href="{{ url('/about-us') }}" @click="open = false">About Us</a>
                    <a href="{{ url('/awards') }}" @click="open = false">Awards</a>
                    <a href="{{ url('/meet-the-team') }}" @click="open = false">Meet the Team</a>
                    <a href="{{ url('/career') }}" @click="open = false">Career – Explore Opportunities</a>
                </details>
                <a href="{{ route('portfolio') }}" @click="open = false">Portfolio</a>
                <a href="{{ route('contact') }}" @click="open = false">Contact</a>
                <a href="{{ url('/free-audit') }}" @click="open = false">Get a Free Audit →</a>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="site-footer">
            <div class="site-footer__inner">
                @if($usesMarketingLayout)
                    @include('partials.home-enquiry', ['variant' => 'contact'])
                @endif
                <section class="site-footer__contact" aria-label="Contact Trivo">
                    <div class="site-footer__contact-copy">
                        @if($usesMarketingLayout)
                            <p>Talk to a growth expert</p>
                            <a class="home-footer-phone" href="tel:+918806409826">+91 88064 09826</a>
                        @else
                        <p class="site-footer__eyebrow">In love with what we do?</p>
                        <h2>Let’s work together to have new approaches, new ideas and new ways of doing growing business.</h2>
                        @endif
                    </div>
                    <div class="site-footer__contact-links">
                        @unless($usesMarketingLayout)
                        <h3>Talk to a growth expert</h3>
                        <a href="tel:+918806409826">+91 88064 09826</a>
                        @endunless
                        <span>Email us</span>
                        <a href="mailto:contact@trivo.in">contact@trivo.in</a>
                        <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>
                    </div>
                    <div class="site-footer__rating" aria-label="Google rating 4.7 out of 5">
                        <span>Reviewed on</span>
                        <strong aria-hidden="true">★★★★★</strong>
                        <b>4.7 Rating</b>
                    </div>
                    <div class="site-footer__partners" aria-label="Our partners">
                        <img src="{{ asset('wp-content/uploads/2023/05/google-cloud.svg') }}" alt="Google Cloud Partner" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/meta-partner.svg') }}" alt="Meta Business Partner" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/google-partner.svg') }}" alt="Google Partner" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/shopify.svg') }}" alt="Shopify Partner" loading="lazy">
                        <img src="{{ asset('wp-content/uploads/2023/05/Untitled-design-3.png') }}" alt="Razorpay Partner" loading="lazy">
                    </div>
                </section>

                <div class="site-footer__links">
                    <section>
                        <h2>Our Services</h2>
                        <a href="{{ url('/paid-search-marketing') }}">Paid search marketing</a>
                        <a href="{{ url('/social-media-marketing') }}">Social Media Marketing</a>
                        <a href="{{ url('/search-engine-optimization') }}">Search Engine Optimization</a>
                        <a href="{{ url('/email-marketing') }}">Email Marketing</a>
                        <a href="{{ url('/google-shopping') }}">Google Shopping</a>
                        <a href="{{ url('/amazon-shopping') }}">Amazon Shopping</a>
                        <a href="{{ url('/graphic-design') }}">Graphic Design</a>
                    </section>
                    <section>
                        <h2>Locations</h2>
                        <a href="{{ url('/seo-company-surat') }}">SEO Company in Surat</a>
                        <a href="{{ url('/seo-company-mumbai') }}">SEO Company in Mumbai</a>
                        <a href="{{ url('/marketing-agency-in-bahrain') }}">Marketing Agency in Bahrain</a>
                        <a href="{{ url('/digital-marketing-agency-in-mumbai') }}">Digital Marketing Agency in Mumbai</a>
                        <a href="{{ url('/social-media-marketing-agency-in-mumbai') }}">Social Media Marketing Agency in Mumbai</a>
                        <a href="{{ url('/performance-marketing-agency-nagpur') }}">Performance Marketing Nagpur</a>
                    </section>
                    <section>
                        <h2>Company</h2>
                        <a href="{{ url('/about-us') }}">About Us</a>
                        <a href="{{ route('portfolio') }}">Portfolio</a>
                        <a href="{{ url('/meet-the-team') }}">Meet the Team</a>
                        <a href="{{ url('/awards') }}">Awards</a>
                        <a href="{{ url('/career') }}">Career at Trivo</a>
                        <a href="{{ route('blog') }}">Blog</a>
                    </section>
                    <section>
                        <h2>Resources</h2>
                        <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                        <a href="{{ route('terms-conditions') }}">Terms &amp; Conditions</a>
                        <a href="{{ route('return-refund-policy') }}">Return &amp; Refund Policy</a>
                        <a href="{{ route('contact') }}">Contact Us</a>
                    </section>
                </div>
            </div>

            <div class="site-footer__bottom">
                <div class="site-footer__bottom-inner">
                    <span>© 2015 - {{ now()->year }} Trivo IT Solution Pvt Ltd</span>
                    <nav aria-label="Legal navigation">
                        <a href="{{ route('terms-conditions') }}">Terms &amp; Conditions</a>
                        <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                        <a href="{{ url('/cookie-policy') }}">Cookie Policy</a>
                        <a href="{{ route('return-refund-policy') }}">Return &amp; Refund Policy</a>
                    </nav>
                    <div class="site-footer__social" aria-label="Social media">
                        <a href="https://www.linkedin.com/company/trivo-in/" aria-label="LinkedIn">
                            <svg class="site-footer__icon site-footer__icon--linkedin" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M20.45 2H3.55C2.69 2 2 2.68 2 3.53v16.94c0 .85.69 1.53 1.55 1.53h16.9c.86 0 1.55-.68 1.55-1.53V3.53C22 2.68 21.31 2 20.45 2ZM8 19H5V9h3v10ZM6.5 7.7a1.75 1.75 0 1 1 0-3.5 1.75 1.75 0 0 1 0 3.5ZM19 19h-3v-4.9c0-1.17-.02-2.67-1.63-2.67-1.63 0-1.88 1.27-1.88 2.59V19h-3V9h2.88v1.37h.04c.4-.76 1.38-1.57 2.84-1.57C18.28 8.8 19 10.77 19 13.34V19Z"/>
                            </svg>
                        </a>
                        <a href="https://twitter.com/trivooffical" aria-label="Twitter">
                            <svg class="site-footer__icon site-footer__icon--twitter" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M23.95 4.57a10 10 0 0 1-2.83.78 4.93 4.93 0 0 0 2.16-2.72 9.86 9.86 0 0 1-3.13 1.2 4.92 4.92 0 0 0-8.39 4.48A13.98 13.98 0 0 1 1.64 2.16a4.92 4.92 0 0 0 1.52 6.57 4.9 4.9 0 0 1-2.23-.62v.06a4.93 4.93 0 0 0 3.95 4.83 4.93 4.93 0 0 1-2.22.08 4.94 4.94 0 0 0 4.6 3.42A9.9 9.9 0 0 1 0 18.54a13.96 13.96 0 0 0 7.56 2.22c9.05 0 14-7.5 14-14v-.64a10 10 0 0 0 2.46-2.55Z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/trivooffical/" aria-label="Instagram">
                            <svg class="site-footer__icon site-footer__icon--instagram" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
                                <circle cx="17.5" cy="6.8" r="1.2"/>
                            </svg>
                        </a>
                        <a href="https://www.facebook.com/trivooffical/" aria-label="Facebook">
                            <svg class="site-footer__icon site-footer__icon--facebook" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M24 12a12 12 0 1 0-13.88 11.86v-8.39H7.08V12h3.04V9.35c0-3 1.79-4.66 4.52-4.66 1.31 0 2.68.23 2.68.23v2.95h-1.51c-1.49 0-1.95.92-1.95 1.86V12h3.32l-.53 3.47h-2.79v8.39A12 12 0 0 0 24 12Z"/>
                            </svg>
                        </a>
                        <a href="https://in.pinterest.com/trivooffical/" aria-label="Pinterest">
                            <svg class="site-footer__icon site-footer__icon--pinterest" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M12 0a12 12 0 0 0-4.37 23.18c-.1-.95-.18-2.4.04-3.43l1.58-6.7s-.4-.8-.4-1.98c0-1.85 1.08-3.23 2.42-3.23 1.14 0 1.69.86 1.69 1.88 0 1.15-.73 2.88-1.1 4.48-.32 1.34.67 2.43 1.99 2.43 2.38 0 4.21-2.51 4.21-6.13 0-3.2-2.3-5.43-5.58-5.43-3.8 0-6.03 2.85-6.03 5.8 0 1.15.44 2.38 1 3.05.11.13.12.25.09.39l-.37 1.5c-.06.24-.19.29-.44.18-1.66-.77-2.7-3.2-2.7-5.15 0-4.2 3.05-8.07 8.8-8.07 4.62 0 8.22 3.3 8.22 7.7 0 4.6-2.9 8.3-6.92 8.3-1.35 0-2.62-.7-3.06-1.53l-.83 3.17c-.3 1.16-1.12 2.62-1.67 3.51A12 12 0 1 0 12 0Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <a class="site-whatsapp" href="https://wa.me/918806409826" target="_blank" rel="noopener noreferrer" aria-label="Chat with Trivo on WhatsApp">
            <svg viewBox="0 0 32 32" aria-hidden="true">
                <path d="M16 3.2A12.6 12.6 0 0 0 5.2 22.3L3.5 28.5l6.4-1.7A12.6 12.6 0 1 0 16 3.2Zm0 22.9c-2 0-3.9-.6-5.5-1.6l-.4-.2-3.8 1 1-3.7-.3-.4A10.4 10.4 0 1 1 16 26.1Zm5.7-7.8c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.3-.5-2.5-1.6-.9-.8-1.6-1.8-1.8-2.1-.2-.3 0-.5.1-.7l.5-.6c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6-.1-.2-.7-1.7-1-2.3-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.1 1.1-1.1 2.6s1.1 3 1.2 3.2c.2.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.2-.3-.3-.6-.4Z"/>
            </svg>
        </a>
    </body>
</html>
