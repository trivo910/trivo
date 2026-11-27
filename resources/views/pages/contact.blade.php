@extends('layouts.public')

@section('title', 'Contact Trivo | Let’s Talk About Your Growth')
@section('description', 'Get in touch with Trivo for digital marketing, web development and growth solutions. Call, email or send us a message.')
@section('canonical', request()->url())
@section('robots', config('seo.default_robots'))

@section('content')
    <article class="contact-page contact-page--career-theme">
        <header class="contact-page__banner">
            <span class="contact-page__banner-orb contact-page__banner-orb--one" aria-hidden="true"></span>
            <span class="contact-page__banner-orb contact-page__banner-orb--two" aria-hidden="true"></span>
            <p class="contact-page__eyebrow">LET’S START A CONVERSATION</p>
            <h1>Contact</h1>
        </header>

        <section class="contact-page__main" aria-labelledby="contact-intro-title">
            <div class="contact-page__details">
                <p class="contact-page__section-kicker"><span></span> WE’RE JUST A MESSAGE AWAY</p>
                <h2 id="contact-intro-title">We’re here to <span>help you grow.</span></h2>
                <p class="contact-page__intro">Feel free to reach out to us through your preferred method of contact. We are eager to connect with you and explore how our digital marketing team can contribute to your success.</p>

                <div class="contact-page__channels">
                    <a class="contact-page__channel" href="tel:+919673537026">
                        <span class="contact-page__channel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.8 9.4 3c.6-.2 1.2.1 1.4.7l1.1 3.1c.2.5 0 1-.4 1.3l-1.7 1.3a14.7 14.7 0 0 0 4.2 4.2l1.3-1.7c.3-.4.8-.6 1.3-.4l3.1 1.1c.6.2.9.8.7 1.4l-.8 2.4c-.2.6-.8 1-1.4 1C10.5 17.4 6.6 13.5 6 5.2c0-.6.4-1.2 1-1.4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><small>PHONE NUMBER</small><strong>+91 96735 37026</strong></span>
                        <span class="contact-page__channel-arrow" aria-hidden="true">↗</span>
                    </a>
                    <a class="contact-page__channel" href="mailto:contact@trivo.in">
                        <span class="contact-page__channel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3.2" y="5" width="17.6" height="14" rx="2.2" stroke="currentColor" stroke-width="1.7"/><path d="m4.2 6.3 7.8 6 7.8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><small>EMAIL ADDRESS</small><strong>contact@trivo.in</strong></span>
                        <span class="contact-page__channel-arrow" aria-hidden="true">↗</span>
                    </a>
                </div>

                <div class="contact-page__map-card">
                    <span class="contact-page__map-icon" aria-hidden="true">
                        <img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="28" height="28" loading="lazy">
                    </span>
                    <div class="contact-page__map-copy">
                        <small>VISIT OUR OFFICE</small>
                        <address>Kalmana, Nagpur,<br>Maharashtra 440035</address>
                    </div>
                    <a href="https://www.google.com/maps/search/Trivo/@21.1618064,79.1568915,17z?authuser=0&amp;entry=ttu&amp;g_ep=EgoyMDI1MDIyNi4xIKXMDSoJLDEwMjExNDUzSAFQAw%3D%3D" target="_blank" rel="noopener noreferrer">View map <span aria-hidden="true">↗</span></a>
                </div>
            </div>

            <form class="contact-page__form" action="{{ url('/enquiries') }}" method="post">
                @csrf
                <input type="hidden" name="form" value="contact">
                <div class="contact-page__honeypot" aria-hidden="true">
                    <label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <span class="contact-page__form-spark" aria-hidden="true">
                    <svg viewBox="0 0 44 44" fill="none"><path d="M22 0c1.9 12.1 8 18.2 22 22-14 3.8-20.1 9.9-22 22C20.1 31.9 14 25.8 0 22 14 18.2 20.1 12.1 22 0Z" fill="currentColor"/><path d="M37 1v10M32 6h10" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <div class="contact-page__form-heading">
                    <p class="contact-page__form-kicker">TELL US WHAT YOU NEED</p>
                    <p>Share a few details and our team will get back to you.</p>
                </div>

                <label class="contact-page__field">
                    <span>Name</span>
                    <input id="contact-name" type="text" name="name" maxlength="150" placeholder="Your name" autocomplete="name" required>
                </label>
                <label class="contact-page__field">
                    <span>Email</span>
                    <input id="contact-email" type="email" name="email" maxlength="254" placeholder="Your email address" autocomplete="email" required>
                </label>
                <label class="contact-page__field">
                    <span>Company name <em>(optional)</em></span>
                    <input id="contact-company" type="text" name="company" maxlength="255" placeholder="Your company" autocomplete="organization">
                </label>
                <label class="contact-page__field">
                    <span>Phone number</span>
                    <input id="contact-phone" type="tel" name="phone" maxlength="50" placeholder="Your contact number" autocomplete="tel" required>
                </label>
                <label class="contact-page__field contact-page__field--message">
                    <span>What can we help you with?</span>
                    <textarea id="contact-message" name="message" maxlength="10000" rows="4" placeholder="Tell us a little about your project or goals..." required></textarea>
                </label>
                <button type="submit">Get in touch <span aria-hidden="true">→</span></button>
                <p class="contact-page__form-note">Your details are only used to respond to your enquiry.</p>
            </form>
        </section>

        <section class="contact-page__locations" aria-labelledby="contact-locations-title">
            <div class="contact-page__locations-heading">
                <p class="contact-page__section-kicker"><span></span> WHERE TO FIND US</p>
                <h2 id="contact-locations-title">Our <span>Locations</span></h2>
                <p>Connect with the Trivo team across our locations.</p>
            </div>
            <div class="contact-page__location-grid">
                <article class="contact-page__location">
                    <span class="contact-page__location-icon" aria-hidden="true"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24" loading="lazy"></span>
                    <span class="contact-page__location-country">BAHRAIN</span>
                    <h3>Riffa</h3>
                    <p>Rd No 1725, Riffa, Bahrain</p>
                    <a href="tel:+97336084903">+973 36084903 <span aria-hidden="true">↗</span></a>
                </article>
                <article class="contact-page__location">
                    <span class="contact-page__location-icon" aria-hidden="true"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24" loading="lazy"></span>
                    <span class="contact-page__location-country">INDIA · MAHARASHTRA</span>
                    <h3>Mumbai</h3>
                    <p>Jogeshwari West, Mumbai,<br>Maharashtra 400102</p>
                    <a href="tel:+919029449582">+91 90294 49582 <span aria-hidden="true">↗</span></a>
                </article>
                <article class="contact-page__location">
                    <span class="contact-page__location-icon" aria-hidden="true"><img src="{{ asset('wp-content/uploads/2023/05/gogle-maps.svg') }}" alt="" width="24" height="24" loading="lazy"></span>
                    <span class="contact-page__location-country">INDIA · GUJARAT</span>
                    <h3>Surat</h3>
                    <p>Surat, Gujarat 394210</p>
                    <a href="tel:+918806409826">+91 88064 09826 <span aria-hidden="true">↗</span></a>
                </article>
            </div>
        </section>
    </article>
@endsection
