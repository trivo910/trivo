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
    carousel.classList.add('is-enhanced');
}

document.querySelectorAll('.home-case-study-carousel').forEach((carousel) => {
    if (carousel instanceof HTMLElement) updateCaseStudyCarousel(carousel);
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

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

document.querySelectorAll('[data-home-enquiry]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const audit = form.closest('.home-enquiry--audit');
        const subject = audit ? 'Free marketing audit enquiry' : 'Marketing proposal enquiry';
        const body = ['Name: ' + data.get('name'), 'Email: ' + data.get('email'), 'Phone: ' + data.get('phone'), 'Company: ' + data.get('company')].join('\n');
        window.location.href = `mailto:contact@trivo.in?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    });
});

document.querySelectorAll('.home-results__testimonials').forEach((container) => {
    const cards = [...container.querySelectorAll('.home-results__testimonial')];
    if (cards.length < 2) return;
    let selected = 0;
    const controls = document.createElement('div');
    controls.className = 'home-testimonial-controls';
    function show(index) {
        selected = (index + cards.length) % cards.length;
        cards.forEach((card, i) => { card.hidden = i !== selected; card.classList.toggle('is-active', i === selected); });
    }
    [-1, 1].forEach((direction) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = direction < 0 ? '\u2190' : '\u2192';
        button.setAttribute('aria-label', direction < 0 ? 'Previous testimonial' : 'Next testimonial');
        button.addEventListener('click', () => show(selected + direction));
        controls.appendChild(button);
    });
    container.appendChild(controls);
    show(0);
});

document.querySelectorAll('.paid-search-clients').forEach((section) => {
    const figures = [...section.querySelectorAll(':scope > figure')];
    if (figures.length < 2) return;
    const track = document.createElement('div');
    track.className = 'paid-search-client-track';
    track.setAttribute('aria-label', 'Client logos');
    figures.forEach((figure) => track.appendChild(figure));
    const controls = document.createElement('div');
    controls.className = 'paid-search-client-controls';
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', 'Choose client logos');
    figures.forEach((figure, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', `Show client ${index + 1}`);
        button.addEventListener('click', () => track.scrollTo({left: figure.offsetLeft - figures[0].offsetLeft, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'}));
        controls.appendChild(button);
    });
    const update = () => {
        const step = figures[0].getBoundingClientRect().width + 16;
        const active = Math.round(track.scrollLeft / step);
        [...controls.children].forEach((button, index) => button.setAttribute('aria-current', String(index === active)));
    };
    track.addEventListener('scroll', update, {passive: true});
    section.append(track, controls);
    update();
});
