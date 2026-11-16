@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Careers at Trivo | Join Our Digital Marketing Team')
@section('description', isset($record) ? ($record->seo_description ?: 'Explore careers at Trivo. Join an innovative digital marketing team focused on growth, creativity, and career development. Apply now and grow with us.') : 'Explore careers at Trivo. Join an innovative digital marketing team focused on growth, creativity, and career development. Apply now and grow with us.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    @php
        $benefits = [
            ['title' => 'Supportive Team', 'copy' => 'Experienced and trained staff to support you through every challenge, with team-building activities along the way.', 'image' => 'ChatGPT-Image-May-9-2026-at-07_29_19-PM-300x300.png'],
            ['title' => 'Innovative Marketing Perspectives', 'copy' => 'Work with an innovative marketing and web development team on challenging, meaningful goals.', 'image' => 'ChatGPT-Image-May-9-2026-at-07_34_34-PM-300x300.png'],
            ['title' => 'Competitive Salary', 'copy' => 'Be recognised for your contribution, with comprehensive insurance and performance-based bonuses.', 'image' => 'ChatGPT-Image-May-9-2026-at-07_37_29-PM-300x300.png'],
            ['title' => 'Career Development Opportunity', 'copy' => 'Grow your skills and build your career in the modern digital marketing world.', 'image' => 'ChatGPT-Image-May-9-2026-at-07_47_48-PM-300x300.png'],
            ['title' => 'Flexible Working Hours', 'copy' => 'Enjoy remote-work opportunities, flexible timing, and paid leave.', 'image' => 'ChatGPT-Image-May-9-2026-at-07_47_52-PM-300x300.png'],
        ];

        $positions = [
            'Digital Marketing Internship',
            'SEO Internship',
            'Social Media Manager Internship',
        ];
    @endphp

    <article class="career-page">
        <header class="career-page__banner">
            <h1>Career – Explore Opportunities</h1>
        </header>

        <section class="career-page__hero" aria-labelledby="career-hero-title">
            <div class="career-page__hero-copy">
                <h2 id="career-hero-title">Join the Dynamic IT and Marketing Team!</h2>
                <p>Be a part of a highly experienced digital marketing and web development company. Trivo IT Solution welcomes dedicated, passionate, and tech-savvy people who are looking to build a growing career in marketing and technology. Bring your creativity, skills, and ideas to help shape digital marketing and deliver personalized results for our customers.</p>
                <p>Join our <a href="{{ url('/performance-marketing-agency-nagpur') }}">digital marketing company</a> and accelerate your career journey.</p>
                <p class="career-page__resume-note">Send your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p>
            </div>
            <div class="career-page__video" data-career-video>
                <img src="https://img.youtube.com/vi/YiWWP5iIyPU/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" referrerpolicy="no-referrer">
                <button type="button" aria-label="Play Trivo digital marketing video">
                    <svg viewBox="0 0 68 48" aria-hidden="true">
                        <path d="M66.5 7.5a8.5 8.5 0 0 0-6-6C55.2 0 34 0 34 0S12.8 0 7.5 1.5a8.5 8.5 0 0 0-6 6A88 88 0 0 0 0 24a88 88 0 0 0 1.5 16.5 8.5 8.5 0 0 0 6 6C12.8 48 34 48 34 48s21.2 0 26.5-1.5a8.5 8.5 0 0 0 6-6A88 88 0 0 0 68 24a88 88 0 0 0-1.5-16.5Z" fill="#f00"/>
                        <path d="m27 34 18-10-18-10z" fill="#fff"/>
                    </svg>
                </button>
            </div>
        </section>

        <section class="career-page__why" id="why-join-trivo" aria-labelledby="career-why-title">
            <div class="career-page__section-heading">
                <h2 id="career-why-title">Why Join Trivo?</h2>
                <p>We are shaping the future of digital marketing, and we’re looking for professionals who deliver accurate outcomes on this journey.</p>
            </div>
            <ul class="career-page__benefits">
                @foreach ($benefits as $benefit)
                    <li>
                        <img src="{{ asset('wp-content/uploads/2026/05/'.$benefit['image']) }}" alt="" width="90" height="90" loading="lazy">
                        <h3>{{ $benefit['title'] }}</h3>
                        <p>{{ $benefit['copy'] }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="career-page__divider" aria-hidden="true"><span></span></div>

        <section class="career-page__openings" aria-labelledby="career-openings-title">
            <h2 id="career-openings-title">View the Open Position</h2>
            <div class="career-page__position-list">
                @foreach ($positions as $position)
                    <details @if ($loop->first) open @endif>
                        <summary>{{ $position }}</summary>
                        <p>Send your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p>
                    </details>
                @endforeach
            </div>
        </section>

        <section class="career-page__culture" aria-label="Our culture and HR initiatives">
            <article>
                <h2>Our Culture</h2>
                <p>Trivo believes in a culture of creativity that builds trust across our team. We value learning, collaboration, and cooperation, and encourage our people to share their opinions and ideas as we work together to achieve our goals.</p>
                <p>We bring creativity, diversity, and innovation to every project. If you have a talent for improving marketing strategies, developing technology, or building stronger client relationships, welcome to our team.</p>
                <p>Join us for a stimulating work environment where your thoughts are valued and your contribution is encouraged.</p>
            </article>
            <article>
                <h2>HR Initiatives</h2>
                <p>We understand that when employees grow, our company grows too. Our HR initiatives support your professional development, personality, and well-being.</p>
                <p>At Trivo, you don’t just work like a robot. We make time for fun and adventurous trips so our employees can relax and recharge from the everyday work environment.</p>
            </article>
        </section>

        <section class="career-page__about" aria-labelledby="career-about-title">
            <h2 id="career-about-title">Know More About Us</h2>
            <p>Trivo believes every client has a distinct set of requirements. We develop web strategies to meet those needs and provide value through a range of talent and expertise.</p>
            <p>We use the latest technology to create functional websites and help clients manage their content independently. Our web development company also offers content and website management, while our SEO work helps clients win new customers.</p>
        </section>

        <section class="career-page__apply" id="apply-now" aria-labelledby="career-apply-title">
            <h2 id="career-apply-title">Apply Now</h2>
            <form data-career-application>
                <div class="career-page__form-grid">
                    <label>
                        <span class="sr-only">Full name</span>
                        <input type="text" name="name" placeholder="Full Name*" autocomplete="name" required>
                    </label>
                    <label>
                        <span class="sr-only">Email address</span>
                        <input type="email" name="email" placeholder="Email Id*" autocomplete="email" required>
                    </label>
                    <label>
                        <span class="sr-only">Contact number</span>
                        <input type="tel" name="phone" placeholder="Contact Number*" autocomplete="tel" required>
                    </label>
                    <label>
                        <span class="sr-only">Apply for the position</span>
                        <input type="text" name="position" placeholder="Apply for the position*" required>
                    </label>
                    <label>
                        <span class="sr-only">Current location</span>
                        <input type="text" name="location" placeholder="Your Current Location*" autocomplete="address-level2" required>
                    </label>
                    <label>
                        <span class="sr-only">Total professional experience in years</span>
                        <input type="text" name="experience" placeholder="Total Professional Experience (Years)*" required>
                    </label>
                </div>
                <label class="career-page__message">
                    <span class="sr-only">Message</span>
                    <textarea name="message" rows="5" placeholder="Message*: Kindly share your cover letter along with your highest qualification and work experience (if any)." required></textarea>
                </label>
                <label class="career-page__resume">
                    <span class="sr-only">Resume file</span>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx">
                </label>
                <p class="career-page__form-note">Your email app will open with your application details. If you selected a resume, attach it to the email before sending.</p>
                <button type="submit">Submit</button>
            </form>
        </section>

        <nav class="career-page__quick-links" aria-label="Career page links">
            <a href="#apply-now"><span aria-hidden="true">✦</span><span>JOIN TRIVO?</span></a>
            <a href="#why-join-trivo"><span aria-hidden="true">✦</span><span>WHY JOIN TRIVO?</span></a>
        </nav>
    </article>
@endsection
