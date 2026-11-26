import Alpine from 'alpinejs';

function selectServiceTab(tab) {
    const container = tab.closest('.home-service-tabs');
    if (!container) return;

    container.querySelectorAll('.home-service-tabs__tab').forEach((item, index) => {
        const selected = item === tab;
        item.setAttribute('aria-selected', String(selected));
        item.tabIndex = selected ? 0 : -1;

        const panelId = item.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;
        if (panel instanceof HTMLElement && container.contains(panel)) {
            panel.hidden = !selected;
        }
    });
}

function updateCaseStudyCarousel(carousel) {
    const track = carousel.querySelector('.home-case-studies');
    const pagination = carousel.querySelector('.home-case-study-pagination');
    if (!(track instanceof HTMLElement) || !(pagination instanceof HTMLElement)) return;

    const slides = Array.from(track.children);
    if (slides.length === 0) return;

    const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
    const visibleSlides = carousel.clientWidth >= 1024 ? 3 : carousel.clientWidth >= 640 ? 2 : 1;
    const slideWidth = Math.max(1, (carousel.clientWidth - gap * (visibleSlides - 1)) / visibleSlides);
    slides.forEach((slide) => {
        if (slide instanceof HTMLElement) slide.style.flexBasis = `${slideWidth}px`;
    });

    const firstSlide = slides[0];
    if (!(firstSlide instanceof HTMLElement)) return;

    const step = firstSlide.getBoundingClientRect().width + gap;
    const maxIndex = Math.max(0, slides.length - visibleSlides);
    const previousIndex = Number.parseInt(carousel.dataset.slideIndex ?? '0', 10) || 0;
    const activeIndex = Math.min(previousIndex, maxIndex);
    const buttons = Array.from(pagination.querySelectorAll('.home-case-study-pagination__button'));

    if (buttons.length !== maxIndex + 1) {
        pagination.replaceChildren();
        for (let index = 0; index <= maxIndex; index++) {
            const button = document.createElement('button');
            button.className = 'home-case-study-pagination__button';
            button.type = 'button';
            button.setAttribute('aria-label', `Go to case study slide ${index + 1}`);
            button.setAttribute('aria-controls', track.id);
            button.dataset.slideIndex = String(index);
            pagination.appendChild(button);
        }
    }

    const updatedButtons = Array.from(pagination.querySelectorAll('.home-case-study-pagination__button'));
    updatedButtons.forEach((button, index) => {
        if (!(button instanceof HTMLButtonElement)) return;
        if (index === activeIndex) button.setAttribute('aria-current', 'true');
        else button.removeAttribute('aria-current');
    });

    carousel.dataset.slideIndex = String(activeIndex);
    track.style.transform = `translate3d(-${activeIndex * step}px, 0, 0)`;
    carousel.querySelectorAll('.home-case-study-carousel__arrow').forEach((arrow) => {
        if (!(arrow instanceof HTMLButtonElement)) return;
        const direction = Number.parseInt(arrow.dataset.carouselDirection ?? '0', 10) || 0;
        arrow.disabled = direction < 0 ? activeIndex === 0 : activeIndex === maxIndex;
    });
    carousel.classList.add('is-enhanced');
}

document.querySelectorAll('.home-case-study-carousel').forEach((carousel) => {
    if (carousel instanceof HTMLElement) updateCaseStudyCarousel(carousel);
});

function updateHomeBlogCarousel(carousel) {
    const track = carousel.querySelector('.home-blog-cards');
    const previous = carousel.querySelector('[data-home-blog-prev]');
    const next = carousel.querySelector('[data-home-blog-next]');
    if (!(track instanceof HTMLElement)
        || !(previous instanceof HTMLButtonElement)
        || !(next instanceof HTMLButtonElement)) return;

    const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    previous.disabled = track.scrollLeft <= 1;
    next.disabled = track.scrollLeft >= maxScroll - 1;
}

document.querySelectorAll('[data-home-blog-carousel]').forEach((carousel) => {
    if (!(carousel instanceof HTMLElement)) return;

    const track = carousel.querySelector('.home-blog-cards');
    if (!(track instanceof HTMLElement)) return;

    updateHomeBlogCarousel(carousel);
    track.addEventListener('scroll', () => updateHomeBlogCarousel(carousel), { passive: true });

    let autoplayTimer = 0;
    const stopAutoplay = () => {
        if (autoplayTimer !== 0) window.clearInterval(autoplayTimer);
        autoplayTimer = 0;
    };
    const startAutoplay = () => {
        if (autoplayTimer !== 0
            || carousel.matches(':hover')
            || carousel.contains(document.activeElement)
            || document.hidden
            || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        autoplayTimer = window.setInterval(() => {
            if (document.hidden) return;

            const firstCard = track.querySelector('.home-blog-card');
            if (!(firstCard instanceof HTMLElement)) return;

            const styles = getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap) || 0;
            const cardStep = firstCard.getBoundingClientRect().width + gap;
            const visibleCards = Math.max(1, Math.round((track.clientWidth + gap) / cardStep));
            const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (track.scrollLeft >= maxScroll - 2) {
                track.scrollTo({ left: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
            } else {
                track.scrollBy({ left: cardStep * visibleCards, behavior: reduceMotion ? 'auto' : 'smooth' });
            }
        }, 5000);
    };

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', (event) => {
        if (!(event.relatedTarget instanceof Node) || !carousel.contains(event.relatedTarget)) startAutoplay();
    });
    carousel.addEventListener('pointerdown', stopAutoplay);
    carousel.addEventListener('pointerup', startAutoplay);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopAutoplay();
        else startAutoplay();
    });
    startAutoplay();
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    const button = target.closest('[data-home-blog-prev], [data-home-blog-next]');
    if (!(button instanceof HTMLButtonElement)) return;

    const carousel = button.closest('[data-home-blog-carousel]');
    const track = carousel?.querySelector('.home-blog-cards');
    const firstCard = track?.querySelector('.home-blog-card');
    if (!(carousel instanceof HTMLElement) || !(track instanceof HTMLElement) || !(firstCard instanceof HTMLElement)) return;

    const styles = getComputedStyle(track);
    const gap = Number.parseFloat(styles.columnGap) || 0;
    const cardStep = firstCard.getBoundingClientRect().width + gap;
    const visibleCards = Math.max(1, Math.round((track.clientWidth + gap) / cardStep));
    const direction = button.hasAttribute('data-home-blog-next') ? 1 : -1;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    track.scrollBy({ left: direction * cardStep * visibleCards, behavior: reduceMotion ? 'auto' : 'smooth' });
});

window.addEventListener('resize', () => {
    document.querySelectorAll('[data-home-blog-carousel]').forEach((carousel) => {
        if (carousel instanceof HTMLElement) updateHomeBlogCarousel(carousel);
    });
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    const arrow = target.closest('.home-case-study-carousel__arrow');
    if (arrow instanceof HTMLButtonElement) {
        const carousel = arrow.closest('.home-case-study-carousel');
        if (!(carousel instanceof HTMLElement)) return;
        const currentIndex = Number.parseInt(carousel.dataset.slideIndex ?? '0', 10) || 0;
        const direction = Number.parseInt(arrow.dataset.carouselDirection ?? '0', 10) || 0;
        const slideCount = carousel.querySelector('.home-case-studies')?.children.length ?? 0;
        const visibleSlides = carousel.clientWidth >= 1024 ? 3 : carousel.clientWidth >= 640 ? 2 : 1;
        const maxIndex = Math.max(0, slideCount - visibleSlides);
        carousel.dataset.slideIndex = String(Math.min(maxIndex, Math.max(0, currentIndex + direction)));
        updateCaseStudyCarousel(carousel);
        return;
    }

    const button = target.closest('.home-case-study-pagination__button');
    if (!(button instanceof HTMLButtonElement)) return;

    const carousel = button.closest('.home-case-study-carousel');
    if (!(carousel instanceof HTMLElement)) return;
    carousel.dataset.slideIndex = button.dataset.slideIndex ?? '0';
    updateCaseStudyCarousel(carousel);
});

let caseStudyResizeFrame = 0;
window.addEventListener('resize', () => {
    if (caseStudyResizeFrame !== 0) cancelAnimationFrame(caseStudyResizeFrame);
    caseStudyResizeFrame = requestAnimationFrame(() => {
        document.querySelectorAll('.home-case-study-carousel.is-enhanced').forEach((carousel) => {
            if (carousel instanceof HTMLElement) updateCaseStudyCarousel(carousel);
        });
        caseStudyResizeFrame = 0;
    });
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    const tab = target.closest('.home-service-tabs__tab');
    if (tab instanceof HTMLButtonElement) selectServiceTab(tab);
});

document.addEventListener('keydown', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLButtonElement) || !target.matches('.home-service-tabs__tab')) return;

    const container = target.closest('.home-service-tabs');
    if (!container) return;
    const tabs = Array.from(container.querySelectorAll('.home-service-tabs__tab'));
    const currentIndex = tabs.indexOf(target);
    let nextIndex = currentIndex;

    if (event.key === 'ArrowRight') nextIndex = (currentIndex + 1) % tabs.length;
    else if (event.key === 'ArrowLeft') nextIndex = (currentIndex + tabs.length - 1) % tabs.length;
    else if (event.key === 'Home') nextIndex = 0;
    else if (event.key === 'End') nextIndex = tabs.length - 1;
    else return;

    event.preventDefault();
    const nextTab = tabs[nextIndex];
    if (nextTab instanceof HTMLButtonElement) {
        nextTab.focus();
        selectServiceTab(nextTab);
    }
});

Alpine.start();

function openMailDraft(recipient, subject, fields) {
    const body = fields.join('\n');
    window.location.href = `mailto:${recipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

document.querySelectorAll('[data-career-video]').forEach((container) => {
    const button = container.querySelector('button');
    if (!(button instanceof HTMLButtonElement)) return;

    button.addEventListener('click', () => {
        const iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube-nocookie.com/embed/YiWWP5iIyPU?rel=0&autoplay=1';
        iframe.title = 'Trivo IT Solution Pvt Ltd | Digital Marketing Services';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.loading = 'eager';
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        container.replaceChildren(iframe);
    });
});


document.querySelectorAll('[data-home-enquiry]:not([data-lead-form])').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const audit = form.closest('.home-enquiry--audit');
        const subject = audit ? 'Free marketing audit enquiry' : form.dataset.homeEnquiry === 'contact' ? 'Contact enquiry' : 'Marketing proposal enquiry';
        const fields = ['Name: ' + data.get('name'), 'Email: ' + data.get('email'), 'Phone: ' + data.get('phone'), 'Company: ' + data.get('company')];
        const message = data.get('message');
        if (typeof message === 'string' && message.trim() !== '') fields.push('Message: ' + message);
        openMailDraft('contact@trivo.in', subject, fields);
    });
});


document.querySelectorAll('[data-career-application]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const fields = [
            'Name: ' + data.get('name'),
            'Email: ' + data.get('email'),
            'Phone: ' + data.get('phone'),
            'Position: ' + data.get('position'),
            'Current location: ' + data.get('location'),
            'Professional experience: ' + data.get('experience') + ' years',
            'Message: ' + data.get('message'),
        ];
        const resume = data.get('resume');
        if (resume instanceof File && resume.name !== '') {
            fields.push('Selected resume: ' + resume.name);
            fields.push('Please attach the selected resume file before sending.');
        }
        openMailDraft('letstalk@trivo.in', 'Career application', fields);
    });
});

document.querySelectorAll('[data-blog-load-more]').forEach((button) => {
    const gridId = button.getAttribute('aria-controls');
    const grid = gridId ? document.getElementById(gridId) : null;
    if (!grid) return;

    button.addEventListener('click', () => {
        [...grid.querySelectorAll('[data-blog-card][hidden]')].slice(0, 6).forEach((card) => {
            card.hidden = false;
        });
        if (grid.querySelector('[data-blog-card][hidden]') === null) button.remove();
    });
});

document.querySelectorAll('.home-results__testimonials').forEach((container) => {
    const cards = [...container.querySelectorAll('.home-results__testimonial')];
    if (cards.length < 2) return;
    const proof = container.closest('.home-results__proof');
    if (!proof) return;

    const controls = document.createElement('div');
    controls.className = 'home-results__nav';
    [-1, 1].forEach((direction) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', direction < 0 ? 'Previous testimonial' : 'Next testimonial');
        button.innerHTML = direction < 0
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
        button.addEventListener('click', () => {
            const firstCard = cards[0];
            if (!(firstCard instanceof HTMLElement)) return;
            const gap = Number.parseFloat(getComputedStyle(container).columnGap) || 0;
            const step = firstCard.getBoundingClientRect().width + gap;
            const atStart = container.scrollLeft <= 4;
            const atEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 4;

            if (direction > 0 && atEnd) {
                container.scrollTo({ left: 0, behavior: 'smooth' });
            } else if (direction < 0 && atStart) {
                container.scrollTo({ left: container.scrollWidth, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: direction * step, behavior: 'smooth' });
            }
        });
        controls.appendChild(button);
    });
    proof.appendChild(controls);
});

const heroAudit = document.querySelector('.home-hero__audit');
const heroEmail = heroAudit?.querySelector('input');
if (heroAudit instanceof HTMLElement && heroEmail instanceof HTMLInputElement) {
    heroEmail.addEventListener('input', () => {
        heroAudit.style.setProperty('--progress', heroEmail.value.trim() ? '100%' : '35%');
    });
}

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const homeHero = document.querySelector('.home-hero');
const homeHeroOrbit = homeHero?.querySelector('.home-hero__orbit');
const homeBudgetInsight = homeHero?.querySelector('.home-budget-insight');

if (homeHero instanceof HTMLElement && homeHeroOrbit instanceof HTMLElement && homeBudgetInsight instanceof HTMLElement) {
    const alignHeroOrbitWithInsight = () => {
        const heroBounds = homeHero.getBoundingClientRect();
        const insightBounds = homeBudgetInsight.getBoundingClientRect();
        if (heroBounds.width === 0 || heroBounds.height === 0) return;

        const centerX = insightBounds.left + insightBounds.width / 2 - heroBounds.left;
        const centerY = insightBounds.top + insightBounds.height / 2 - heroBounds.top;
        homeHero.style.setProperty('--home-orbit-center-x', `${centerX / heroBounds.width * 100}%`);
        homeHero.style.setProperty('--home-orbit-center-y', `${centerY / heroBounds.height * 100}%`);

        const viewportWidth = window.innerWidth;
        let targetWidths;
        let targetHeights;
        if (viewportWidth <= 560) {
            targetWidths = [Math.min(heroBounds.width * .9258, 370), Math.min(heroBounds.width * .701, 284.3), Math.min(heroBounds.width * .4761, 191.8)];
            targetHeights = [Math.min(heroBounds.width * .7, 280), Math.min(heroBounds.width * .53, 215), Math.min(heroBounds.width * .36, 145)];
        } else if (viewportWidth <= 1100) {
            targetWidths = [Math.min(heroBounds.width * .6348, 423), Math.min(heroBounds.width * .4893, 323), Math.min(heroBounds.width * .34385, 231.4)];
            targetHeights = [Math.min(heroBounds.width * .48, 320), Math.min(heroBounds.width * .37, 245), Math.min(heroBounds.width * .26, 175)];
        } else {
            targetWidths = [heroBounds.width * .64, heroBounds.width * .54, heroBounds.width * .35];
            targetHeights = [Math.min(heroBounds.width * .391, 368), Math.min(heroBounds.width * .3105, 287.5), Math.min(heroBounds.width * .23, 212.75)];
        }

        const availableWidth = Math.max(0, 2 * (Math.min(centerX, heroBounds.width - centerX) - 12));
        const availableHeight = Math.max(0, 2 * (Math.min(centerY, heroBounds.height - centerY) - 12));
        ['one', 'two', 'three'].forEach((name, index) => {
            homeHero.style.setProperty(`--home-orbit-ring-${name}-size`, `${Math.min(targetWidths[index], availableWidth)}px`);
            homeHero.style.setProperty(`--home-orbit-ring-${name}-height`, `${Math.min(targetHeights[index], availableHeight)}px`);
        });
    };

    const sphereTracks = [
        { ring: homeHeroOrbit.querySelector('.home-hero__orbit-ring--one'), sphere: homeHeroOrbit.querySelector('.home-hero__orbit-sphere--large'), duration: 34000, startAngle: -Math.PI / 2, direction: 1 },
        { ring: homeHeroOrbit.querySelector('.home-hero__orbit-ring--two'), sphere: homeHeroOrbit.querySelector('.home-hero__orbit-sphere--medium'), duration: 27000, startAngle: Math.PI / 2, direction: -1 },
        { ring: homeHeroOrbit.querySelector('.home-hero__orbit-ring--three'), sphere: homeHeroOrbit.querySelector('.home-hero__orbit-sphere--small'), duration: 21000, startAngle: Math.PI, direction: 1 },
    ].filter((track) => track.ring instanceof HTMLElement && track.sphere instanceof HTMLElement);

    const placeOrbitSpheres = (time) => {
        sphereTracks.forEach(({ ring, sphere, duration, startAngle, direction }) => {
            const angle = startAngle + time / duration * Math.PI * 2 * direction;
            const radiusX = ring.clientWidth / 2;
            const radiusY = ring.clientHeight / 2;
            sphere.style.left = `${ring.clientWidth / 2 + radiusX * Math.cos(angle)}px`;
            sphere.style.top = `${ring.clientHeight / 2 + radiusY * Math.sin(angle)}px`;
            sphere.style.right = 'auto';
            sphere.style.bottom = 'auto';
            sphere.style.transform = 'translate(-50%, -50%)';
        });
    };

    alignHeroOrbitWithInsight();
    placeOrbitSpheres(0);
    if (!reduceMotion) {
        const animateOrbitSpheres = (time) => {
            placeOrbitSpheres(time);
            requestAnimationFrame(animateOrbitSpheres);
        };
        requestAnimationFrame(animateOrbitSpheres);
    }
    window.addEventListener('resize', alignHeroOrbitWithInsight, { passive: true });
    if ('ResizeObserver' in window) {
        const heroOrbitObserver = new ResizeObserver(alignHeroOrbitWithInsight);
        heroOrbitObserver.observe(homeHero);
        heroOrbitObserver.observe(homeBudgetInsight);
    }
    document.fonts?.ready.then(alignHeroOrbitWithInsight);
}

const budgetDotMotion = document.querySelector('.home-budget-chart__dot animateMotion');
if (!reduceMotion && budgetDotMotion && typeof budgetDotMotion.beginElement === 'function') {
    budgetDotMotion.beginElement();
}

document.querySelectorAll('[data-count-target]').forEach((counter) => {
    if (!(counter instanceof HTMLElement)) return;
    const target = Number.parseInt(counter.dataset.countTarget ?? '', 10);
    if (!Number.isFinite(target)) return;
    const initial = Number.parseInt(counter.dataset.countStart ?? '0', 10);
    const startValue = Number.isFinite(initial) ? initial : 0;
    const format = (value) => counter.dataset.countFormat === 'grouped'
        ? new Intl.NumberFormat('en-IN').format(value)
        : String(value);
    if (reduceMotion) {
        counter.textContent = format(target);
        return;
    }

    const mode = counter.dataset.countMode;
    const duration = mode === 'fast-then-slow' ? 1850 : (startValue > target ? 2100 : 1650);
    const start = performance.now();
    counter.textContent = format(startValue);
    const updateCounter = (now) => {
        const elapsed = now - start;
        let value;

        if (mode === 'fast-then-slow') {
            const fastPhase = Math.min(1, elapsed / 550);
            const fastValue = 550 * (1 - Math.pow(1 - fastPhase, 3));
            const slowPhase = Math.min(1, Math.max(0, (elapsed - 550) / 1300));
            value = elapsed < 550
                ? fastValue
                : 550 + (target - 550) * (1 - Math.pow(1 - slowPhase, 3));
        } else {
            const progress = Math.min(1, elapsed / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            value = startValue + (target - startValue) * eased;
        }

        counter.textContent = format(Math.round(value));
        if (elapsed < duration) requestAnimationFrame(updateCounter);
    };
    requestAnimationFrame(updateCounter);
});

const premiumResults = document.querySelector('.home-results--premium');
if (premiumResults instanceof HTMLElement) {
    const counters = [...premiumResults.querySelectorAll('[data-results-counter]')]
        .filter((counter) => counter instanceof HTMLElement);
    const setFinalResults = () => {
        counters.forEach((counter) => {
            const target = Number.parseInt(counter.dataset.resultsTarget ?? '', 10);
            if (!Number.isFinite(target)) return;
            counter.textContent = `${new Intl.NumberFormat('en-IN').format(target)}${counter.dataset.resultsSuffix ?? ''}`;
        });
    };
    const animateResults = () => {
        counters.forEach((counter) => {
            const target = Number.parseInt(counter.dataset.resultsTarget ?? '', 10);
            if (!Number.isFinite(target)) return;

            const suffix = counter.dataset.resultsSuffix ?? '';
            const start = performance.now();
            const duration = 1450;
            const update = (now) => {
                const progress = Math.min(1, (now - start) / duration);
                const value = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                counter.textContent = `${new Intl.NumberFormat('en-IN').format(value)}${suffix}`;
                if (progress < 1) requestAnimationFrame(update);
            };
            counter.textContent = `0${suffix}`;
            requestAnimationFrame(update);
        });
    };

    if (reduceMotion || !('IntersectionObserver' in window)) {
        premiumResults.classList.add('is-visible');
        setFinalResults();
    } else {
        premiumResults.classList.add('is-ready');
        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            premiumResults.classList.add('is-visible');
            animateResults();
            observer.disconnect();
        }, { threshold: 0.22 });
        observer.observe(premiumResults);
    }
}

document.querySelectorAll('[data-home-enquiry]').forEach((form) => {
    const inputs = [...form.querySelectorAll('.home-enquiry__fields input')];
    if (inputs.length === 0) return;

    const updateProgress = () => {
        const filled = inputs.filter((input) => input.value.trim() !== '').length;
        form.style.setProperty('--progress', `${35 + 65 * filled / inputs.length}%`);
    };

    inputs.forEach((input) => input.addEventListener('input', updateProgress));
    updateProgress();
});

let horizontalCarouselId = 0;

function setupHorizontalCarousel(section, track, items, controlsClass, trackId, itemLabel = 'client logos') {
    if (items.length < 2) return;

    track.id = `${trackId}-${++horizontalCarouselId}`;
    track.classList.add('is-enhanced');
    const controls = document.createElement('div');
    controls.className = controlsClass;
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', `Choose ${itemLabel}`);
    let activeIndex = 0;

    const update = () => {
        const firstItem = items[0];
        const step = firstItem.getBoundingClientRect().width + (Number.parseFloat(getComputedStyle(track).columnGap) || 0);
        if (step <= 0 || track.clientWidth === 0) return;

        const visibleItems = Math.max(1, Math.round(track.clientWidth / step));
        const lastIndex = Math.max(0, items.length - visibleItems);
        activeIndex = Math.min(lastIndex, Math.round(track.scrollLeft / step));
        const pageCount = lastIndex + 1;

        if (controls.children.length !== pageCount) {
            controls.replaceChildren();
            for (let index = 0; index < pageCount; index++) {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('aria-label', `Show ${itemLabel} ${index + 1}`);
                button.setAttribute('aria-controls', track.id);
                button.addEventListener('click', () => {
                    const targetLeft = items[index].getBoundingClientRect().left - track.getBoundingClientRect().left + track.scrollLeft;
                    track.scrollTo({
                        left: targetLeft,
                        behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
                    });
                });
                controls.appendChild(button);
            }
        }

        [...controls.children].forEach((button, index) => {
            if (index === activeIndex) button.setAttribute('aria-current', 'true');
            else button.removeAttribute('aria-current');
        });
    };

    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    section.appendChild(controls);
    update();
}

document.querySelectorAll('.paid-search-clients').forEach((section) => {
    const figures = [...section.querySelectorAll(':scope > figure')];
    if (figures.length < 2) return;
    const track = document.createElement('div');
    track.className = 'paid-search-client-track';
    track.setAttribute('aria-label', 'Client logos');
    figures.forEach((figure) => track.appendChild(figure));
    section.appendChild(track);
    setupHorizontalCarousel(section, track, figures, 'paid-search-client-controls', 'paid-search-client-track');
});

// Reveal the paid search service content as it enters view. Keep the client
// marquee out of this animation so its existing motion stays untouched.
document.querySelectorAll('.service-page--paid-search-marketing').forEach((page) => {
    const sections = [...page.querySelectorAll('.paid-search-content > .paid-search-intro, .paid-search-content > .paid-search-row, .paid-search-content > .paid-search-results')];
    if (!sections.length || !('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    page.classList.add('js-reveal-ready');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    sections.forEach((section) => observer.observe(section));
});

document.querySelectorAll('.service-page--digital-marketing-agency-in-mumbai .dm-tools, .service-page--social-media-marketing-agency-in-mumbai .sm-tools').forEach((section) => {
    const track = section.querySelector('.location-logo-grid');
    if (!track) return;
    setupHorizontalCarousel(section, track, [...track.children], 'paid-search-client-controls', 'digital-marketing-tools');
});

document.querySelectorAll('.about-page__clients').forEach((section) => {
    const track = section.querySelector(':scope > .about-page__client-track');
    if (!(track instanceof HTMLUListElement)) return;
    setupHorizontalCarousel(section, track, [...track.children], 'about-page__client-controls', 'about-page-client-track');
});

document.querySelectorAll('.team-page__gallery').forEach((section) => {
    const track = section.querySelector(':scope > .team-page__gallery-track');
    if (!(track instanceof HTMLUListElement)) return;
    setupHorizontalCarousel(section, track, [...track.children], 'team-page__gallery-controls', 'team-page-gallery', 'team photos');
});

// Case-study related projects use native scrolling, including without JavaScript.
document.querySelectorAll('[data-case-carousel]').forEach(carousel => {
    const track = carousel.querySelector('.case-cards');
    carousel.querySelector('.case-carousel__previous').addEventListener('click', () => track.scrollBy({left: -track.clientWidth}));
    carousel.querySelector('.case-carousel__next').addEventListener('click', () => track.scrollBy({left: track.clientWidth}));
    const dots = [...carousel.querySelectorAll('[data-case-slide]')];
    const step = () => (track.firstElementChild?.getBoundingClientRect().width ?? 0) + 30;
    dots.forEach((dot, index) => dot.addEventListener('click', () => track.scrollTo({left: index * step()})));
    track.addEventListener('scroll', () => {
        const current = Math.round(track.scrollLeft / step());
        dots.forEach((dot, index) => dot.setAttribute('aria-pressed', String(current === index)));
    }, {passive: true});
});
