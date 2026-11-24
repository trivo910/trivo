@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Meet the Team | Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Meet the Trivo team of revenue-focused digital marketing specialists.') : 'Meet the Trivo team of revenue-focused digital marketing specialists.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="team-page">
        <header class="team-page__banner">
            <h1>Meet the Team</h1>
        </header>

        <section class="team-page__hero" aria-labelledby="team-hero-title">
            <div>
                <h2 id="team-hero-title">Meet the Trivo revenue revolutionaries</h2>
                <p>We believe in having a talented team of self-starters that want to deliver the best results for our clients.</p>
            </div>
            <img src="{{ asset('wp-content/uploads/2025/03/Untitled-design-2.png') }}" alt="Team members joining hands around a table" width="520" height="416" fetchpriority="high">
        </section>

        <section class="team-page__beliefs" aria-labelledby="team-beliefs-title">
            <div class="team-page__belief-intro">
                <div>
                    <p class="team-page__eyebrow">What We Believe In</p>
                    <h2 id="team-beliefs-title">We believe in making the world a better place</h2>
                </div>
                <p>We achieve this by scaling impactful businesses. Our focus is on promoting our clients’ brands and effectively disseminating their message worldwide. We refuse to settle for the status quo, constantly striving to expand our own business with the same level of honesty and integrity we employ to enhance our clients’ enterprises.</p>
            </div>
            <ul class="team-page__values">
                <li>Transparency</li>
                <li>Accountability</li>
                <li>Lifelong Learning</li>
                <li>Ambition</li>
            </ul>
        </section>

        <section class="team-page__testimonial" aria-label="Client testimonial from Ronak Patel">
            <figure>
                <div class="team-page__quote-mark" aria-hidden="true">“</div>
                <blockquote>
                    <p>I am very grateful to the Trivo IT Solution team and especially to Mr. Kashif Khan for their outstanding support and expertise. He guided us in selecting the right product that perfectly suited our needs, ensuring a smooth and efficient process. The professionalism, dedication, and technical excellence of the Trivo IT Solution team made the entire experience seamless and hassle-free. I am truly impressed with their attention to detail, innovative approach, and commitment to delivering a high-quality product.</p>
                </blockquote>
                <p class="team-page__testimonial-author">Ronak Patel</p>
                <img src="{{ asset('wp-content/uploads/2025/03/Lina-1747.png') }}" alt="Customer review illustration with a five-star rating" width="574" height="477" loading="lazy">
            </figure>
        </section>

        <section class="team-page__mission" aria-labelledby="team-mission-title">
            <div class="team-page__gallery" aria-label="Team photos">
                <ul class="team-page__gallery-track">
                    <li><img src="{{ asset('wp-content/uploads/2023/06/pexels-fauxels-3228812.jpg') }}" alt="A team celebrating their ideas together" width="1468" height="800" loading="lazy"></li>
                    <li><img src="{{ asset('wp-content/uploads/2023/06/pexels-mikael-blomkvist-6476186.jpg') }}" alt="Colleagues collaborating around a computer" width="1468" height="800" loading="lazy"></li>
                    <li><img src="{{ asset('wp-content/uploads/2023/06/pexels-alena-darmel-7710141.jpg') }}" alt="Illustration of a creative team working together" width="1468" height="800" loading="lazy"></li>
                </ul>
            </div>
            <div class="team-page__mission-copy">
                <h2 id="team-mission-title">Our mission</h2>
                <p>At Trivo, our mission is to drive meaningful change by positively impacting 3,000,000 people around the world. We achieve this by working hand-in-hand with scientists, engineers, businesses, and non-profits to advance and accelerate the adoption of life-changing innovations. Through collaboration, technology, and a commitment to progress, we strive to create a better future for all.</p>
            </div>
        </section>

        <section class="team-page__opportunities" aria-labelledby="team-opportunities-title">
            <img src="{{ asset('wp-content/uploads/2023/05/star.svg') }}" alt="" width="40" height="40" loading="lazy">
            <h2 id="team-opportunities-title">Explore opportunities with us today</h2>
            <p>Joining Trivo means joining our extended family. As we work together to create impactful marketing campaigns, we prioritize not only the success of our team members but also their well-being and the well-being of their families.</p>
            <a href="{{ route('contact') }}">Join our team <span aria-hidden="true">→</span></a>
        </section>

        @include('partials.home-clients', ['headingId' => 'team-clients-title'])
    </article>
@endsection
