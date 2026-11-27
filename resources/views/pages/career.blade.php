@extends('layouts.public')

@section('title', 'Careers at Trivo | Join Our Digital Marketing Team')
@section('description', 'Explore careers at Trivo. Join an innovative digital marketing team focused on growth, creativity, and career development. Apply now and grow with us.')
@section('canonical', request()->url())
@section('robots', config('seo.default_robots'))

@section('content')
    <article class="career-page career-page--redesign">
        <header class="career-page__banner">
            <span class="career-page__banner-orb career-page__banner-orb--one" aria-hidden="true"></span>
            <span class="career-page__banner-orb career-page__banner-orb--two" aria-hidden="true"></span>
            <p class="career-page__eyebrow">PEOPLE • IDEAS • IMPACT</p>
            <h1>Career <span>at Trivo</span></h1>
            <p class="career-page__banner-subtitle">Find your place. Build your skills. Create meaningful work.</p>
        </header>

        <section class="career-page__hero" aria-labelledby="career-hero-title">
            <div class="career-page__hero-copy">
                <p class="career-page__eyebrow"><span></span> GROW WITH US</p>
                <h2 id="career-hero-title">Join the <span>Dynamic IT and Marketing Team!</span></h2>
                <p>Be a part of a highly experienced digital marketing and web development company. Trivo IT Solution welcomes dedicated, passionate, and tech-savvy people who are looking to build a growing career in marketing and technology. Bring your creativity, skills, and ideas to help shape digital marketing and deliver personalized results for our customers.</p>
                <p>Join our <a href="{{ url('/performance-marketing-agency-nagpur') }}">digital marketing company</a> and accelerate your career journey.</p>
                <div class="career-page__hero-actions">
                    <a class="career-page__button career-page__button--primary" href="#open-positions">Explore Open Positions <span aria-hidden="true">→</span></a>
                    <a class="career-page__button career-page__button--secondary" href="mailto:letstalk@trivo.in">Send Your Resume</a>
                </div>
                <p class="career-page__resume-note">Have a question? Email your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p>
            </div>
            <div class="career-page__video-wrap">
                <div class="career-page__video" data-career-video>
                    <img src="https://img.youtube.com/vi/YiWWP5iIyPU/hqdefault.jpg" alt="Preview of Trivo's digital marketing and workplace culture video" width="480" height="360" fetchpriority="high" referrerpolicy="no-referrer">
                    <button type="button" aria-label="Play Trivo digital marketing video">
                        <svg viewBox="0 0 68 48" aria-hidden="true" focusable="false">
                            <path d="M66.5 7.5a8.5 8.5 0 0 0-6-6C55.2 0 34 0 34 0S12.8 0 7.5 1.5a8.5 8.5 0 0 0-6 6A88 88 0 0 0 0 24a88 88 0 0 0 1.5 16.5 8.5 8.5 0 0 0 6 6C12.8 48 34 48 34 48s21.2 0 26.5-1.5a8.5 8.5 0 0 0 6-6A88 88 0 0 0 68 24a88 88 0 0 0-1.5-16.5Z" fill="#f00"/>
                            <path d="m27 34 18-10-18-10z" fill="#fff"/>
                        </svg>
                    </button>
                    <span class="career-page__video-caption">A glimpse into life at Trivo</span>
                </div>
                <div class="career-page__hero-note"><span class="career-page__hero-note-icon" aria-hidden="true">✦</span><span><strong>Bring your ideas.</strong> Make an impact with a team that helps you grow.</span></div>
            </div>
        </section>

        <section class="career-page__why" id="why-join-trivo" aria-labelledby="career-why-title">
            <div class="career-page__section-heading">
                <p class="career-page__eyebrow">YOUR NEXT CHAPTER STARTS HERE</p>
                <h2 id="career-why-title">Why Join <span>Trivo?</span></h2>
                <p>We are shaping the future of digital marketing, and we’re looking for professionals who deliver accurate outcomes on this journey.</p>
            </div>
            <ul class="career-page__benefits">
                <li>
                    <span class="career-page__benefit-icon career-page__benefit-icon--blue" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M24 5 39 11v11c0 10-6 16-15 21C15 38 9 32 9 22V11L24 5Z"/><path d="m16 24 5 5 11-12"/></svg></span>
                    <h3>Supportive Team</h3>
                    <p>Experienced and trained staff to support you through every challenge, with team-building activities along the way.</p>
                </li>
                <li>
                    <span class="career-page__benefit-icon career-page__benefit-icon--purple" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M17 27 11 33a7 7 0 0 0 10 10l8-8a7 7 0 0 0 0-10"/><path d="m31 21 6-6A7 7 0 0 0 27 5l-8 8a7 7 0 0 0 0 10"/><path d="m18 30 12-12"/></svg></span>
                    <h3>Innovative Marketing Perspectives</h3>
                    <p>Work with an innovative marketing and web development team on challenging, meaningful goals.</p>
                </li>
                <li>
                    <span class="career-page__benefit-icon career-page__benefit-icon--gold" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M14 7h20v9a10 10 0 0 1-20 0V7Z"/><path d="M14 11H7v4a9 9 0 0 0 9 9M34 11h7v4a9 9 0 0 1-9 9M24 26v9M16 41h16M19 35h10"/></svg></span>
                    <h3>Competitive Salary</h3>
                    <p>Be recognised for your contribution, with comprehensive insurance and performance-based bonuses.</p>
                </li>
                <li>
                    <span class="career-page__benefit-icon career-page__benefit-icon--green" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M8 38V24h8v14M20 38V15h8v23M32 38V8h8v30"/><path d="m9 16 10-7 8 2 12-7"/></svg></span>
                    <h3>Career Development Opportunity</h3>
                    <p>Grow your skills and build your career in the modern digital marketing world.</p>
                </li>
                <li>
                    <span class="career-page__benefit-icon career-page__benefit-icon--pink" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="17"/><path d="M24 14v11l8 5M24 4v4M44 24h-4M8 24H4"/></svg></span>
                    <h3>Flexible Working Hours</h3>
                    <p>Enjoy remote-work opportunities, flexible timing, and paid leave.</p>
                </li>
            </ul>
        </section>

        <section class="career-page__openings" id="open-positions" aria-labelledby="career-openings-title">
            <div class="career-page__section-heading">
                <p class="career-page__eyebrow">FIND YOUR FIT</p>
                <h2 id="career-openings-title">Explore <span>Open Positions</span></h2>
                <p>Start with a role that matches your curiosity, strengths and ambition.</p>
            </div>
            <div class="career-page__position-list">
                <details open>
                    <summary><span class="career-page__position-mark" aria-hidden="true">01</span><span class="career-page__position-name">Digital Marketing Internship</span><span class="career-page__position-type">Internship</span><span class="career-page__position-toggle" aria-hidden="true"></span></summary>
                    <div class="career-page__position-detail"><p>Send your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p><a class="career-page__position-apply" href="#apply-now">Apply for this role <span aria-hidden="true">→</span></a></div>
                </details>
                <details>
                    <summary><span class="career-page__position-mark" aria-hidden="true">02</span><span class="career-page__position-name">SEO Internship</span><span class="career-page__position-type">Internship</span><span class="career-page__position-toggle" aria-hidden="true"></span></summary>
                    <div class="career-page__position-detail"><p>Send your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p><a class="career-page__position-apply" href="#apply-now">Apply for this role <span aria-hidden="true">→</span></a></div>
                </details>
                <details>
                    <summary><span class="career-page__position-mark" aria-hidden="true">03</span><span class="career-page__position-name">Social Media Manager Internship</span><span class="career-page__position-type">Internship</span><span class="career-page__position-toggle" aria-hidden="true"></span></summary>
                    <div class="career-page__position-detail"><p>Send your updated resume to <a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a>.</p><a class="career-page__position-apply" href="#apply-now">Apply for this role <span aria-hidden="true">→</span></a></div>
                </details>
            </div>
        </section>

        <section class="career-page__culture" aria-label="Our culture and HR initiatives">
            <article class="career-page__culture-card career-page__culture-card--culture">
                <span class="career-page__culture-icon" aria-hidden="true">✦</span>
                <p class="career-page__eyebrow">LIFE AT TRIVO</p>
                <h2>Our Culture</h2>
                <p>Trivo believes in a culture of creativity that builds trust across our team. We value learning, collaboration, and cooperation, and encourage our people to share their opinions and ideas as we work together to achieve our goals.</p>
                <p>We bring creativity, diversity, and innovation to every project. If you have a talent for improving marketing strategies, developing technology, or building stronger client relationships, welcome to our team.</p>
                <p>Join us for a stimulating work environment where your thoughts are valued and your contribution is encouraged.</p>
            </article>
            <article class="career-page__culture-card career-page__culture-card--hr">
                <span class="career-page__culture-icon" aria-hidden="true">◎</span>
                <p class="career-page__eyebrow">PEOPLE FIRST</p>
                <h2>HR Initiatives</h2>
                <p>We understand that when employees grow, our company grows too. Our HR initiatives support your professional development, personality, and well-being.</p>
                <p>At Trivo, you don’t just work like a robot. We make time for fun and adventurous trips so our employees can relax and recharge from the everyday work environment.</p>
                <a href="#apply-now">Grow with our team <span aria-hidden="true">→</span></a>
            </article>
        </section>

        <section class="career-page__about" aria-labelledby="career-about-title">
            <div class="career-page__about-copy">
                <p class="career-page__eyebrow">GET TO KNOW US</p>
                <h2 id="career-about-title">Know More <span>About Us</span></h2>
                <p>Trivo believes every client has a distinct set of requirements. We develop web strategies to meet those needs and provide value through a range of talent and expertise.</p>
                <p>We use the latest technology to create functional websites and help clients manage their content independently. Our web development company also offers content and website management, while our SEO work helps clients win new customers.</p>
                <a class="career-page__text-link" href="{{ url('/about-us') }}">Discover Trivo <span aria-hidden="true">→</span></a>
            </div>
            <div class="career-page__about-art" aria-hidden="true">
                <span class="career-page__art-orbit career-page__art-orbit--one"></span>
                <span class="career-page__art-orbit career-page__art-orbit--two"></span>
                <div class="career-page__art-card career-page__art-card--main"><span class="career-page__art-spark">✦</span><strong>Ideas</strong><span>that move forward</span></div>
                <div class="career-page__art-card career-page__art-card--small"><span>↗</span><strong>Grow together</strong></div>
                <span class="career-page__art-dot career-page__art-dot--one"></span><span class="career-page__art-dot career-page__art-dot--two"></span>
            </div>
        </section>

        <section class="career-page__apply" id="apply-now" aria-labelledby="career-apply-title">
            <div class="career-page__apply-intro">
                <p class="career-page__eyebrow">YOUR FUTURE STARTS HERE</p>
                <h2 id="career-apply-title">Ready to make your <span>next move?</span></h2>
                <p>Tell us a little about yourself. Share your details and the role you’re interested in, and our team can take it from there.</p>
                <div class="career-page__apply-email"><span aria-hidden="true">✉</span><div><small>Prefer email?</small><a href="mailto:letstalk@trivo.in">letstalk@trivo.in</a></div></div>
            </div>
            <form data-career-application>
                <div class="career-page__form-heading"><span>APPLICATION FORM</span><strong>Apply Now</strong></div>
                <div class="career-page__form-grid">
                    <label><span class="sr-only">Full name</span><input type="text" name="name" placeholder="Full Name*" autocomplete="name" required></label>
                    <label><span class="sr-only">Email address</span><input type="email" name="email" placeholder="Email Id*" autocomplete="email" required></label>
                    <label><span class="sr-only">Contact number</span><input type="tel" name="phone" placeholder="Contact Number*" autocomplete="tel" required></label>
                    <label><span class="sr-only">Apply for the position</span><input type="text" name="position" placeholder="Apply for the position*" required></label>
                    <label><span class="sr-only">Current location</span><input type="text" name="location" placeholder="Your Current Location*" autocomplete="address-level2" required></label>
                    <label><span class="sr-only">Total professional experience in years</span><input type="text" name="experience" placeholder="Total Professional Experience (Years)*" required></label>
                </div>
                <label class="career-page__message"><span class="sr-only">Message</span><textarea name="message" rows="5" placeholder="Message*: Kindly share your cover letter along with your highest qualification and work experience (if any)." required></textarea></label>
                <label class="career-page__resume"><span class="career-page__file-label">Attach your resume <small>(PDF, DOC or DOCX)</small></span><input type="file" name="resume" accept=".pdf,.doc,.docx"></label>
                <p class="career-page__form-note">Your email app will open with your application details. If you selected a resume, attach it to the email before sending.</p>
                <button type="submit" class="career-page__button career-page__button--primary">Submit Application <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <nav class="career-page__quick-links" aria-label="Career page links">
            <a href="#apply-now"><span aria-hidden="true">✦</span> JOIN TRIVO</a>
            <a href="#why-join-trivo"><span aria-hidden="true">✦</span> WHY JOIN TRIVO?</a>
        </nav>
    </article>
@endsection
