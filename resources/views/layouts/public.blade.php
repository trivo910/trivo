@php($usesMarketingLayout = request()->routeIs('home') || \App\Services\Content\ServicePageLayout::supports(request()->path()))
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
                    <a href="{{ url('/marketing-solutions') }}" @if (request()->is('marketing-solutions')) aria-current="page" @endif>Marketing Solutions</a>
                    <a href="{{ url('/about-us') }}" @if (request()->is('about-us')) aria-current="page" @endif>Who We Are</a>
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
                <a href="{{ url('/about-us') }}" @click="open = false">Who We Are</a>
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
                        <a href="https://www.linkedin.com/company/trivo-in/" aria-label="LinkedIn">in</a>
                        <a href="https://twitter.com/trivooffical" aria-label="Twitter">t</a>
                        <a href="https://www.instagram.com/trivooffical/" aria-label="Instagram">ig</a>
                        <a href="https://www.facebook.com/trivooffical/" aria-label="Facebook">f</a>
                        <a href="https://in.pinterest.com/trivooffical/" aria-label="Pinterest">p</a>
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
