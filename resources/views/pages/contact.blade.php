@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Contact Trivo | Digital Marketing Consultant in India')
@section('description', isset($record) ? ($record->seo_description ?: 'Speak with Trivo about SEO, PPC, social media growth, website strategy, and performance marketing for your business.') : 'Speak with Trivo about SEO, PPC, social media growth, website strategy, and performance marketing for your business.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="contact-page">
        <header class="contact-page__banner">
            <h1>Contact</h1>
        </header>

        <section class="contact-page__main" aria-labelledby="contact-intro-title">
            <div class="contact-page__details">
                <h2 id="contact-intro-title">We’re here to help you grow</h2>
                <p>Feel free to reach out to us through your preferred method of contact. We are eager to connect with you and explore how our digital marketing team can contribute to your success.</p>

                <div class="contact-page__channels">
                    <div>
                        <span>Phone number:</span>
                        <a href="tel:+919673537026">+91 96735 37026</a>
                    </div>
                    <div>
                        <span>Email address:</span>
                        <a href="mailto:contact@trivo.in">contact@trivo.in</a>
                    </div>
                </div>

                <div class="contact-page__map">
                    <span class="contact-page__map-icon">
                        <img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24">
                    </span>
                    <address>Kalmana, Nagpur,<br> Maharashtra 440035</address>
                    <a href="https://www.google.com/maps/search/Trivo/@21.1618064,79.1568915,17z?authuser=0&amp;entry=ttu&amp;g_ep=EgoyMDI1MDIyNi4xIKXMDSoJLDEwMjExNDUzSAFQAw%3D%3D" target="_blank" rel="noopener noreferrer">View on map</a>
                </div>
            </div>

            <form
                class="contact-page__form"
                action="{{ route('enquiries.store') }}"
                method="post"
            >
                @csrf
                <input type="hidden" name="form" value="contact">
                @include('partials.enquiry-errors', ['formKey' => 'contact'])
                @include('partials.enquiry-honeypot')
                <svg class="contact-page__spark" viewBox="0 0 44 44" fill="none" aria-hidden="true">
                    <path d="M22 0c1.9 12.1 8 18.2 22 22-14 3.8-20.1 9.9-22 22C20.1 31.9 14 25.8 0 22 14 18.2 20.1 12.1 22 0Z" fill="currentColor"/>
                    <path d="M37 1v10M32 6h10" stroke="currentColor" stroke-width="2"/>
                </svg>
                <label class="sr-only" for="contact-name">Name</label>
                <input id="contact-name" type="text" name="name" maxlength="150" value="{{ old('form') === 'contact' ? old('name') : '' }}" placeholder="Name" autocomplete="name" aria-label="Name" required>

                <label class="sr-only" for="contact-email">Email</label>
                <input id="contact-email" type="email" name="email" maxlength="254" value="{{ old('form') === 'contact' ? old('email') : '' }}" placeholder="Email" autocomplete="email" aria-label="Email" required>

                <label class="sr-only" for="contact-company">Company name</label>
                <input id="contact-company" type="text" name="company" maxlength="255" value="{{ old('form') === 'contact' ? old('company') : '' }}" placeholder="Company name" autocomplete="organization" aria-label="Company name">

                <label class="sr-only" for="contact-phone">Phone number</label>
                <input id="contact-phone" type="tel" name="phone" maxlength="50" value="{{ old('form') === 'contact' ? old('phone') : '' }}" placeholder="Phone number" autocomplete="tel" aria-label="Phone number" required>

                <label class="sr-only" for="contact-message">What can we help you with?</label>
                <textarea id="contact-message" name="message" maxlength="10000" rows="4" placeholder="What can we help you with?" aria-label="What can we help you with?" required>{{ old('form') === 'contact' ? old('message') : '' }}</textarea>

                <button type="submit">Get in touch <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <section class="contact-page__locations" aria-labelledby="contact-locations-title">
            <h2 id="contact-locations-title">Locations</h2>
            <div class="contact-page__location-grid">
                <article class="contact-page__location">
                    <span class="contact-page__location-icon"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24"></span>
                    <p>Rd No 1725, Riffa, Bahrain</p>
                    <span class="contact-page__location-label">Phone number:</span>
                    <a href="tel:+97336084903">+973 36084903</a>
                </article>
                <article class="contact-page__location">
                    <span class="contact-page__location-icon"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24"></span>
                    <p>Jogeshwari West, Mumbai,<br> Maharashtra 400102</p>
                    <span class="contact-page__location-label">Phone number:</span>
                    <a href="tel:+919029449582">+91 90294 49582</a>
                </article>
                <article class="contact-page__location">
                    <span class="contact-page__location-icon"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24"></span>
                    <p>Surat, Gujarat<br>394210</p>
                    <span class="contact-page__location-label">Phone number:</span>
                    <a href="tel:+918806409826">+91 88064 09826</a>
                </article>
            </div>
        </section>
    </article>
@endsection
