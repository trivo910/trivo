@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Meet the Team - Trivo')
@section('description', isset($record) ? ($record->seo_description ?: '') : 'Meet the Numerique revenue revolutionaries')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="team-page team-page--redesign">
        <header class="team-page__banner">
            <span class="team-page__banner-orb" aria-hidden="true"></span>
            <h1>Meet the Team</h1>
        </header>

        <div class="team-page__container">
            <section class="team-page__hero" aria-labelledby="team-hero-title">
                <div class="team-page__hero-copy">
                    <p class="team-page__eyebrow"><span aria-hidden="true"></span> THE PEOPLE BEHIND TRIVO</p>
                    <h2 id="team-hero-title">Meet the Trivo </br><span>revenue revolutionaries</span></h2>
                    <p>We believe in having a talented team of self-starters that want to deliver the best results for our clients.</p>
                    <a class="team-page__button" href="#team-opportunities">Explore opportunities <span aria-hidden="true">→</span></a>
                </div>
                <div class="team-page__hero-media">
                    <span class="team-page__hero-orbit team-page__hero-orbit--one" aria-hidden="true"></span>
                    <span class="team-page__hero-orbit team-page__hero-orbit--two" aria-hidden="true"></span>
                    <img
                        src="{{ asset('wp-content/uploads/2025/03/Untitled-design-2.png') }}"
                        alt="Team members joining hands around a table"
                        width="520"
                        height="416"
                        fetchpriority="high"
                    >
                    <span class="team-page__hero-tag"><span aria-hidden="true"></span> One team. Shared ambition.</span>
                </div>
            </section>

            <section class="team-page__belief" aria-labelledby="team-beliefs-title">
                <div class="team-page__belief-intro">
                    <div>
                        <p class="team-page__eyebrow">WHAT WE BELIEVE IN</p>
                        <h2 id="team-beliefs-title">We believe in making the world a <span>better place</span></h2>
                    </div>
                    <p>We achieve this by scaling impactful businesses. Our focus is on promoting our clients’ brands and effectively disseminating their message worldwide. We refuse to settle for the status quo, constantly striving to expand our own business with the same level of honesty and integrity we employ to enhance our clients’ enterprises.</p>
                </div>
                <ul class="team-page__values" aria-label="Our values">
                    <li class="team-page__value team-page__value--blue">
                        <span class="team-page__value-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none"><path d="M24 5 39 11v11c0 10-6.4 16.7-15 21-8.6-4.3-15-11-15-21V11L24 5Z" stroke="currentColor" stroke-width="2.8" stroke-linejoin="round"/><path d="m16.5 23.5 5 5 10-11" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><strong>Transparency</strong><small>Clear communication and honest work.</small></span>
                    </li>
                    <li class="team-page__value team-page__value--purple">
                        <span class="team-page__value-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none"><path d="M8 25 19 36a6 6 0 0 0 8.5 0l12-12a6 6 0 0 0-8.5-8.5l-4 4M8 25l7-7a6 6 0 0 1 8.5 0l6 6M8 25l-3-3a5 5 0 0 1 7-7l3 3" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><strong>Accountability</strong><small>Taking ownership of every outcome.</small></span>
                    </li>
                    <li class="team-page__value team-page__value--gold">
                        <span class="team-page__value-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none"><path d="M8 9.5A4.5 4.5 0 0 1 12.5 5H40v34H12.5A4.5 4.5 0 0 0 8 43V9.5Z" stroke="currentColor" stroke-width="2.8" stroke-linejoin="round"/><path d="M8 10h27M16 17h16M16 24h13M16 31h9" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/></svg>
                        </span>
                        <span><strong>Lifelong Learning</strong><small>Staying curious, growing every day.</small></span>
                    </li>
                    <li class="team-page__value team-page__value--green">
                        <span class="team-page__value-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none"><path d="M8 38 36 10M20 10h16v16" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 27v11h11" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><strong>Ambition</strong><small>Always reaching for a better standard.</small></span>
                    </li>
                </ul>
            </section>

            <section class="team-page__testimonial" aria-label="Client testimonial from Ronak Patel">
                <div class="team-page__testimonial-copy">
                    <p class="team-page__eyebrow">A CLIENT EXPERIENCE</p>
                    <span class="team-page__quote-mark" aria-hidden="true">“</span>
                    <blockquote>
                        <p>I am very grateful to the Trivo IT Solution team and especially to Mr. Kashif Khan for their outstanding support and expertise. He guided us in selecting the right product that perfectly suited our needs, ensuring a smooth and efficient process. The professionalism, dedication, and technical excellence of the Trivo IT Solution team made the entire experience seamless and hassle-free. I am truly impressed with their attention to detail, innovative approach, and commitment to delivering a high-quality product.</p>
                    </blockquote>
                    <p class="team-page__testimonial-author">Ronak Patel</p>
                </div>
                <div class="team-page__testimonial-media">
                    <img
                        src="{{ asset('wp-content/uploads/2025/03/Lina-1747.png') }}"
                        alt="Customer review illustration with a five-star rating"
                        width="574"
                        height="477"
                        loading="lazy"
                    >
                </div>
            </section>

            <section class="team-page__mission" aria-labelledby="team-mission-title">
                <div class="team-page__mission-gallery" aria-label="Team photos">
                    <figure class="team-page__mission-photo team-page__mission-photo--main">
                        <img src="{{ asset('wp-content/uploads/2023/06/pexels-fauxels-3228812.jpg') }}" alt="A team celebrating their ideas together" width="1468" height="800" loading="lazy">
                    </figure>
                    <figure class="team-page__mission-photo team-page__mission-photo--top">
                        <img src="{{ asset('wp-content/uploads/2023/06/pexels-mikael-blomkvist-6476186.jpg') }}" alt="Colleagues collaborating around a computer" width="1468" height="800" loading="lazy">
                    </figure>
                    <figure class="team-page__mission-photo team-page__mission-photo--bottom">
                        <img src="{{ asset('wp-content/uploads/2023/06/pexels-alena-darmel-7710141.jpg') }}" alt="A creative team working together" width="1468" height="800" loading="lazy">
                    </figure>
                    <span class="team-page__mission-stamp" aria-hidden="true">BETTER<br>TOGETHER</span>
                </div>
                <div class="team-page__mission-copy">
                    <p class="team-page__eyebrow">OUR PURPOSE</p>
                    <h2 id="team-mission-title">Our mission is to make a <span>meaningful impact</span></h2>
                    <p>At Trivo, our mission is to drive meaningful change by positively impacting 3,000,000 people around the world. We achieve this by working hand-in-hand with scientists, engineers, businesses, and non-profits to advance and accelerate the adoption of life-changing innovations. Through collaboration, technology, and a commitment to progress, we strive to create a better future for all.</p>
                    <div class="team-page__mission-note"><span aria-hidden="true">✦</span> Progress through people, ideas and collaboration.</div>
                </div>
            </section>

            <section class="team-page__opportunitiess" id="team-opportunities" aria-labelledby="team-opportunities-title">
                <span class="team-page__opportunities-orb team-page__opportunities-orb--one" aria-hidden="true"></span>
                <span class="team-page__opportunities-orb team-page__opportunities-orb--two" aria-hidden="true"></span>
                <img src="{{ asset('wp-content/uploads/2023/05/star.svg') }}" alt="" width="40" height="40" loading="lazy">
                <p class="team-page__eyebrow">GROW WITH US</p>
                <h2 id="team-opportunities-title">Explore opportunities with us today</h2>
                <p>Joining Trivo means joining our extended family. As we work together to create impactful marketing campaigns, we prioritize not only the success of our team members but also their well-being and the well-being of their families.</p>
                <a class="team-page__button team-page__button--blue" href="{{ url('/contact') }}">Join our team <span aria-hidden="true">→</span></a>
            </section>

            <section class="team-page__clients" aria-labelledby="team-clients-title">
                <div class="team-page__section-heading">
                    <p class="team-page__eyebrow">TRUSTED PARTNERSHIPS</p>
                    <h2 id="team-clients-title">Clients we work with</h2>
                    <span aria-hidden="true"></span>
                </div>
                @include('partials.home-clients', ['headingId' => 'team-clients-list-title'])
            </section>
        </div>
    </article>
@endsection
