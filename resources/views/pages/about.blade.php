@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'About Trivo | Digital Marketing Agency in India')
@section('description', isset($record) ? ($record->seo_description ?: '') : 'Learn how Trivo helps ambitious brands grow with performance marketing, content strategy, design, and conversion-focused digital systems.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="about-page">
        <header class="about-page__banner">
            <h1>About Us</h1>
        </header>

        <section class="about-page__hero" aria-labelledby="about-hero-title">
            <h2 id="about-hero-title">Digital marketing solutions designed to generate revenue for your business</h2>
            <img
                src="{{ asset('wp-content/uploads/2025/03/teamwork-brainstorming-meeting-new-startup-project-workplace.jpg') }}"
                alt="A marketing team collaborating around a table"
                width="768"
                height="373"
                fetchpriority="high"
            >
        </section>

        <section class="about-page__story" aria-label="About Trivo">
            <p>We understand that every client has a unique set of requirements and are capable of crafting web strategies to meet those needs. Our work is focused on delivering value, building visibility, and helping businesses grow through digital marketing.</p>
            <p>We are an India-based web development and digital marketing solutions company. From SEO and social media to paid campaigns and design, we create practical strategies that help businesses reach the right audience and grow with confidence.</p>
            <p>We believe the best digital marketing is accessible to everyone and not limited to big budgets. We work closely with our clients, keep communication open, and continually improve campaigns to deliver meaningful results.</p>
        </section>

        <section class="about-page__values" aria-labelledby="about-values-title">
            <h2 id="about-values-title">Our values</h2>
            <div class="about-page__value-grid">
                <article class="about-page__value">
                    <span class="about-page__value-icon" aria-hidden="true">T</span>
                    <h3>Transparency</h3>
                    <p>No smoke and mirrors here. We believe in clear, genuine communication and share the “why” behind the work we do and the way we do it.</p>
                </article>
                <article class="about-page__value">
                    <span class="about-page__value-icon" aria-hidden="true">I</span>
                    <h3>Integrity</h3>
                    <p>We prioritise honesty and integrity, always striving to build relationships based on trust and understanding that is earned through consistent, responsible work.</p>
                </article>
                <article class="about-page__value">
                    <span class="about-page__value-icon" aria-hidden="true">S</span>
                    <h3>Simplicity</h3>
                    <p>We believe that being an expert should not mean sounding complicated. We make strategies clear, practical, and easy to put into action.</p>
                </article>
                <article class="about-page__value">
                    <span class="about-page__value-icon" aria-hidden="true">P</span>
                    <h3>Performance</h3>
                    <p>Being in digital marketing means data is a given. We use it to make better decisions, improve results, and keep your business growing.</p>
                </article>
            </div>
        </section>

        <section class="about-page__provider" aria-labelledby="about-provider-title">
            <p>Marketing Solutions Provider</p>
            <div>
                <h2 id="about-provider-title">We’re a leader in digital marketing solutions</h2>
                <div>
                    <p>At Trivo, we deliver cutting-edge digital marketing solutions that help businesses thrive in an ever-evolving digital landscape. With a proven track record of driving results, we leverage the latest technologies, data-driven strategies, and industry expertise to create tailored marketing campaigns that maximise brand visibility, customer engagement, and revenue growth.</p>
                    <a href="{{ url('/marketing-solutions') }}">Marketing Solutions <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>

        <section class="about-page__testimonial" aria-labelledby="about-testimonial-title">
            <h2 id="about-testimonial-title">What our happy customers are saying</h2>
            <figure>
                <blockquote>
                    <p>“I am very grateful to the Trivo IT Solution team and mostly to Mr. Kashif Khan. He helped us select proper products as per our need. Proud to say, my portal was developed and designed by Trivo IT Solution.”</p>
                </blockquote>
                <figcaption>
                    <strong>Ranak Patel</strong>
                    <span>Director, United Arab Travels &amp; Tours London UK</span>
                </figcaption>
                <div class="about-page__review-footer">
                    <div class="about-page__rating" aria-label="Rated five out of five stars">
                        <span aria-hidden="true">★★★★★</span>
                        <small>5000+ client reviews</small>
                    </div>
                    <a
                        class="about-page__reviews-link"
                        href="https://share.google/V9Qv6dImscMPQSWGX"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        View all reviews <span aria-hidden="true">→</span>
                    </a>
                </div>
            </figure>
        </section>

        <section class="about-page__clients" aria-labelledby="about-clients-title">
            <h2 id="about-clients-title">Clients We work with</h2>
            <ul class="about-page__client-track" aria-label="Client logos">
                <li><img src="{{ asset('wp-content/uploads/2026/03/Sanjari-2.png') }}" alt="Sanjari Recycling &amp; Travels" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Polla-Tech.png') }}" alt="Polla Tech" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Al-Bushra.png') }}" alt="Al Bushra &amp; Travels" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Mumbai-Darshan.png') }}" alt="Mumbai Darshan" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Brady-Morris.png') }}" alt="Brady Morris" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Stock-Sovet.png') }}" alt="Stock Sovet" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Stop-Over.png') }}" alt="Stop Over" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Quba-Dates.png') }}" alt="Quba Dates" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Medikash.png') }}" alt="Medikash" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/MYVN.png') }}" alt="MYVN" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Al-Arafat.png') }}" alt="Al Arafat" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Al-Rida.png') }}" alt="Al Rida" loading="lazy" width="300" height="135"></li>
                <li><img src="{{ asset('wp-content/uploads/2026/03/Gulshan-Tours.png') }}" alt="Gulshan Tours" loading="lazy" width="300" height="135"></li>
            </ul>
        </section>
    </article>
@endsection
