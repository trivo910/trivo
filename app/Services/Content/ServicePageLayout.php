<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ServicePageLayout
{
    public const PATHS = [
        '/paid-search-marketing', '/social-media-marketing', '/search-engine-optimization',
        '/email-marketing', '/google-shopping', '/amazon-shopping', '/rate-optimization', '/graphic-design',
    ];

    public const LOCATION_PATHS = [
        '/seo-company-surat', '/seo-company-mumbai', '/marketing-agency-in-bahrain',
        '/digital-marketing-agency-in-mumbai', '/social-media-marketing-agency-in-mumbai',
        '/performance-marketing-agency-nagpur',
    ];

    public static function supports(string $path): bool
    {
        return in_array('/'.trim($path, '/'), [...self::PATHS, ...self::LOCATION_PATHS], true);
    }

    public function render(string $html, string $path, array $testimonials = []): string
    {
        if ($path === '/paid-search-marketing') {
            return app(PaidSearchPageLayout::class)->render($html);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $body = $document->getElementsByTagName('body')->item(0);
        $nodes = array_values(array_filter(iterator_to_array($body->childNodes), fn ($node) => $node instanceof DOMElement));
        $output = $document->createElement('div');
        $output->setAttribute('class', 'paid-search-content service-content');
        $groups = [];
        $section = null;
        $hidden = false;
        $clients = false;
        foreach ($nodes as $node) {
            $tag = strtolower($node->tagName);
            $text = strtolower(trim(preg_replace('/\s+/', ' ', $node->textContent)));
            if ($path === '/rate-optimization' && $tag === 'h6' && $text === 'what you get') {
                break;
            }
            if ($tag === 'h1') {
                continue;
            }
            if (in_array($tag, ['h2', 'h3', 'h4'], true)
                && (preg_match('/clients we works? with/i', $text) || str_starts_with($text, 'trusted by '))) {
                $section = $document->createElement('section');
                $section->setAttribute('class', 'paid-search-clients');
                $output->appendChild($section);
                $clients = true;
                $hidden = false;
                // The common carousel uses one heading level for its styling.
                $heading = $document->createElement('h3');
                $heading->textContent = trim($node->textContent);
                $section->appendChild($heading);
                continue;
            }
            if ($clients && $tag !== 'figure') {
                $clients = false;
                $section = null;
            }
            if (($tag === 'h6' && $text === 'what you get')
                || ($path === '/google-shopping' && $tag === 'h3' && $text === 'enhanced product discovery')) {
                $hidden = true;
                $section = $document->createElement('div');
                $section->setAttribute('hidden', '');
                $output->appendChild($section);
            }
            if ($hidden || $clients) {
                $section->appendChild($node);
                continue;
            }
            if ($tag === 'br') {
                // Keep breaks separating short standalone lines (for example SEO benefits).
                if ($section?->lastChild instanceof DOMElement && $section->lastChild->tagName === 'strong') {
                    $section->appendChild($node);
                }
                continue;
            }
            $isHeading = in_array($tag, ['h2', 'h3', 'h4', 'h6'], true);
            $onlyHeadings = $section && count(array_filter(iterator_to_array($section->childNodes), fn ($child) => $child instanceof DOMElement && ! in_array($child->tagName, ['h2', 'h3', 'h4', 'h6'], true))) === 0;
            if (! $section || ($isHeading && ! $onlyHeadings)) {
                $section = $document->createElement('section');
                $section->setAttribute('class', 'service-section');
                $output->appendChild($section);
                $groups[] = $section;
            }
            $section->appendChild($node);
        }

        // Restore illustrations that preceded their heading in the flattened import.
        foreach ($groups as $index => $group) {
            $images = array_values(array_filter(iterator_to_array($group->childNodes), fn ($node) => $node instanceof DOMElement && $node->tagName === 'img'));
            $hasCopy = count(array_filter(iterator_to_array($group->childNodes), fn ($node) => $node instanceof DOMElement && in_array($node->tagName, ['p', 'ul', 'ol'], true))) > 0;
            if ($path !== '/graphic-design' && isset($groups[$index + 1]) && (count($images) > 1 || (count($images) === 1 && ! $hasCopy))) {
                $next = $groups[$index + 1];
                $next->insertBefore(end($images), $next->firstChild);
                $next->setAttribute('data-image-first', 'true');
            }
        }

        foreach ($groups as $index => $group) {
            $copy = $document->createElement('div');
            $copy->setAttribute('class', 'service-copy');
            $media = $document->createElement('div');
            $media->setAttribute('class', 'service-media');
            foreach (iterator_to_array($group->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->tagName === 'img') {
                    $child->setAttribute('loading', 'lazy');
                    $media->appendChild($child);
                } else {
                    $copy->appendChild($child);
                }
            }
            $class = 'service-section';
            if ($media->hasChildNodes()) {
                $class .= ' service-section--illustrated';
                if ($group->getAttribute('data-image-first') === 'true') {
                    $class .= ' service-section--reverse';
                }
            } elseif ($copy->getElementsByTagName('ul')->length > 0) {
                $class .= ' service-section--list';
            } else {
                $class .= ' service-section--text';
            }
            if ($index === 0) {
                $class .= ' service-section--intro';
            }
            if ($path === '/graphic-design') {
                $headingLabels = [];
                foreach ((new DOMXPath($document))->query('./*[self::h2 or self::h3 or self::h4]', $copy) as $heading) {
                    $headingLabels[] = strtolower(trim($heading->textContent));
                }
                $label = $headingLabels[0] ?? '';
                if (in_array($label, ['creative designs', 'high quality', 'fast delivery'], true)) {
                    $class .= ' service-section--benefit';
                }
                if (str_contains($label, 'proof is in the numbers')) {
                    $class .= ' service-section--stats';
                }
                if (in_array('we do', $headingLabels, true)
                    && in_array('professional graphics designing for your brand', $headingLabels, true)) {
                    $class .= ' service-section--brand-intro';
                }
                if (in_array('why choose us?', $headingLabels, true)) {
                    $class .= ' service-section--why-choose';
                }
            } elseif ($path === '/rate-optimization') {
                $heading = (new DOMXPath($document))->query('./*[self::h2 or self::h3 or self::h4]', $copy)->item(0);
                $label = strtolower(trim($heading?->textContent ?? ''));
                if (str_starts_with($label, 'combining search engine marketing')) {
                    $class .= ' service-section--cro-explanation';
                }
                if (str_starts_with($label, 'real certified google ads professionals')) {
                    $class .= ' service-section--certified';
                }
            }
            $group->setAttribute('class', $class);
            $group->appendChild($copy);
            if ($media->hasChildNodes()) {
                $group->appendChild($media);
            }
            if ($path !== '/performance-marketing-agency-nagpur') {
                $this->featureCards($document, $copy);
            }
            if ($path === '/search-engine-optimization') {
                $this->seoServicePoints($document, $copy);
            }
            if (in_array($path, self::LOCATION_PATHS, true)) {
                $this->locationDetails($document, $copy);
                if ($copy->getElementsByTagName('details')->length > 0) {
                    $group->setAttribute('class', $group->getAttribute('class').' service-section--accordion');
                }
                $figures = (new DOMXPath($document))->query('./figure', $copy);
                if ($path !== '/performance-marketing-agency-nagpur' && $figures->length > 1) {
                    $gallery = $document->createElement('div');
                    $gallery->setAttribute('class', 'location-logo-grid');
                    $copy->insertBefore($gallery, $figures->item(0));
                    foreach (iterator_to_array($figures) as $figure) {
                        $gallery->appendChild($figure);
                    }
                }
            }
        }
        if ($path === '/performance-marketing-agency-nagpur') {
            $this->performanceMarketingAgencyLayout($document, $output, $groups);
        }
        if ($path === '/rate-optimization') {
            $this->tabs($document, $groups, [
                'UI/UX Design', 'Web Development', '3D Designs', 'Digital Marketing Design', 'Motion Graphics',
            ]);
        } elseif ($path === '/email-marketing') {
            $this->tabs($document, $groups, ['PPC audit services', 'Remarketing services', 'Geofencing ad services', 'Social media ad services']);
        } elseif ($path === '/graphic-design') {
            $this->graphicDesignHero($document, $output, $groups);
            $this->graphicDesignBanner($document, $groups);
            $this->graphicDesignStatsAndReviews($document, $output, $groups, $testimonials);
            $this->tabs($document, $groups, ['Motion Graphics', 'UI/UX Design', 'Marketing Design']);
        }
        foreach ((new DOMXPath($document))->query('.//a[not(@href)]', $output) ?: [] as $link) {
            $text = strtolower(trim($link->textContent));
            $link->setAttribute('href', match (true) {
                str_contains($text, 'about') => url('/about-us'),
                str_contains($text, 'solutions') => url('/marketing-solutions'),
                str_contains($text, 'audit') => url('/free-audit'),
                default => route('contact'),
            });
        }
        foreach ((new DOMXPath($document))->query('.//ul', $output) ?: [] as $list) {
            if (trim($list->textContent) === '' && $list->getElementsByTagName('li')->length > 0) {
                $list->setAttribute('class', 'service-rating');
                $list->setAttribute('aria-label', $list->getElementsByTagName('li')->length.' stars');
            }
        }

        return $document->saveHTML($output);
    }

    private function graphicDesignHero(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $intro = null;
        $summary = null;

        foreach ($groups as $group) {
            $copy = $xpath->query(
                './div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]',
                $group,
            )->item(0);
            if (! $copy instanceof DOMElement) {
                continue;
            }

            if (str_contains(' '.$group->getAttribute('class').' ', ' service-section--intro ')) {
                $intro = $group;
            }

            foreach ($copy->getElementsByTagName('h4') as $heading) {
                if (str_starts_with(trim($heading->textContent), '2,120,240,368')) {
                    $summary = $group;
                    break 2;
                }
            }
        }

        if (! $intro instanceof DOMElement) {
            return;
        }

        $introCopy = $xpath->query(
            './div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]',
            $intro,
        )->item(0);
        $media = $xpath->query(
            './div[contains(concat(" ", normalize-space(@class), " "), " service-media ")]',
            $intro,
        )->item(0);
        if (! $introCopy instanceof DOMElement || ! $media instanceof DOMElement) {
            return;
        }

        $images = array_values(array_filter(
            iterator_to_array($media->getElementsByTagName('img')),
            fn ($image): bool => $image instanceof DOMElement,
        ));
        if (count($images) < 2) {
            return;
        }

        $collage = $document->createElement('div');
        $collage->setAttribute('class', 'graphic-design-hero__collage');
        foreach (['left', 'upper-right'] as $shape) {
            $block = $document->createElement('div');
            $block->setAttribute('class', 'graphic-design-hero__shape graphic-design-hero__shape--'.$shape);
            $block->setAttribute('aria-hidden', 'true');
            $collage->appendChild($block);
        }

        $artwork = $document->createElement('div');
        $artwork->setAttribute('class', 'graphic-design-hero__artwork');
        $images[1]->setAttribute('loading', 'eager');
        $images[1]->setAttribute('fetchpriority', 'high');
        $artwork->appendChild($images[1]);
        $collage->appendChild($artwork);

        $award = $document->createElement('div');
        $award->setAttribute('class', 'graphic-design-hero__award');
        $images[0]->setAttribute('loading', 'eager');
        if ($images[0]->getAttribute('alt') === '') {
            $images[0]->setAttribute('alt', 'Award trophy');
        }
        $award->appendChild($images[0]);
        $awardLabel = $document->createElement('span');
        $awardLabel->appendChild($document->createTextNode('Award Winning Agency'));
        $award->appendChild($awardLabel);
        $collage->appendChild($award);

        $talkLink = null;
        $expertsLink = null;
        foreach (iterator_to_array($introCopy->getElementsByTagName('a')) as $link) {
            $label = strtolower(trim($link->textContent));
            if (! $talkLink && str_contains($label, 'talk')) {
                $talkLink = $link;
            } elseif (! $expertsLink && str_contains($label, 'experts')) {
                $expertsLink = $link;
            }
        }

        $callout = $document->createElement('div');
        $callout->setAttribute('class', 'graphic-design-hero__callout');
        $calloutTitle = $document->createElement('p');
        $calloutTitle->appendChild($document->createTextNode('Get Stunning Designs Today!'));
        $callout->appendChild($calloutTitle);
        if ($talkLink instanceof DOMElement) {
            $talkLink->setAttribute('class', 'graphic-design-hero__talk');
            $callout->appendChild($talkLink);
        } else {
            $talkLink = $document->createElement('a');
            $talkLink->setAttribute('class', 'graphic-design-hero__talk');
            $talkLink->setAttribute('href', url('/contact'));
            $talkLink->appendChild($document->createTextNode('Let’s talk'));
            $callout->appendChild($talkLink);
        }
        $collage->appendChild($callout);

        $intro->removeChild($media);
        $intro->appendChild($collage);

        $proof = $document->createElement('div');
        $proof->setAttribute('class', 'graphic-design-hero__proof');
        $experts = $document->createElement('div');
        $experts->setAttribute('class', 'graphic-design-hero__experts');
        $avatars = $document->createElement('div');
        $avatars->setAttribute('class', 'graphic-design-hero__avatars');
        $avatars->setAttribute('aria-hidden', 'true');
        foreach (array_slice($images, 2) as $image) {
            $avatars->appendChild($image);
        }
        if ($avatars->hasChildNodes()) {
            $experts->appendChild($avatars);
        }
        if ($expertsLink instanceof DOMElement) {
            $expertsLink->setAttribute('class', 'graphic-design-hero__experts-link');
            $experts->appendChild($expertsLink);
        }
        $proof->appendChild($experts);

        if ($summary instanceof DOMElement) {
            $summaryCopy = $xpath->query(
                './div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]',
                $summary,
            )->item(0);
            if ($summaryCopy instanceof DOMElement) {
                $revenue = $document->createElement('div');
                $revenue->setAttribute('class', 'graphic-design-hero__revenue');
                foreach (iterator_to_array($summaryCopy->childNodes) as $child) {
                    if (! $child instanceof DOMElement) {
                        continue;
                    }

                    if ($child->tagName === 'h4' && str_starts_with(trim($child->textContent), '2,120,240,368')) {
                        $child->setAttribute('class', 'graphic-design-hero__number');
                        $revenue->appendChild($child);
                    } elseif ($child->tagName === 'p' && str_contains(strtolower($child->textContent), 'revenue driven')) {
                        $child->setAttribute('class', 'graphic-design-hero__revenue-label');
                        $revenue->appendChild($child);
                    }
                }
                if ($revenue->hasChildNodes()) {
                    $proof->appendChild($revenue);
                }

                $rating = $document->createElement('div');
                $rating->setAttribute('class', 'graphic-design-hero__rating');
                foreach (iterator_to_array($summaryCopy->childNodes) as $child) {
                    if ($child instanceof DOMElement
                        && (($child->tagName === 'ul' && $child->getElementsByTagName('li')->length >= 5)
                            || ($child->tagName === 'p' && str_contains(strtolower($child->textContent), 'client reviews')))) {
                        $rating->appendChild($child);
                    }
                }
                if ($rating->hasChildNodes()) {
                    $proof->appendChild($rating);
                }
            }
            if ($summary->parentNode === $output) {
                $output->removeChild($summary);
            }
        }

        if ($talkLink instanceof DOMElement && $talkLink->parentNode === $introCopy) {
            $introCopy->removeChild($talkLink);
        }
        $intro->appendChild($proof);
        $classes = array_values(array_filter(
            explode(' ', $intro->getAttribute('class')),
            fn (string $class): bool => $class !== 'service-section--illustrated',
        ));
        $intro->setAttribute('class', implode(' ', $classes));
    }

    private function graphicDesignBanner(DOMDocument $document, array $groups): void
    {
        $xpath = new DOMXPath($document);
        foreach ($groups as $group) {
            $heading = $xpath->query(
                './div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]/*[self::h2 or self::h3 or self::h4]',
                $group,
            )->item(0);
            if (strtolower(trim($heading?->textContent ?? '')) !== 'fast delivery') {
                continue;
            }

            $media = $xpath->query(
                './div[contains(concat(" ", normalize-space(@class), " "), " service-media ")]',
                $group,
            )->item(0);
            if (! $media instanceof DOMElement || ! $media->hasChildNodes()) {
                return;
            }

            $group->removeChild($media);
            $classes = array_values(array_filter(
                explode(' ', $group->getAttribute('class')),
                fn (string $class): bool => $class !== 'service-section--illustrated',
            ));
            $group->setAttribute('class', implode(' ', $classes));

            $banner = $document->createElement('div');
            $banner->setAttribute('class', 'service-design-banner');
            $banner->setAttribute('aria-hidden', 'true');
            $banner->appendChild($media);
            $group->parentNode->insertBefore($banner, $group->nextSibling);

            return;
        }
    }

    /**
     * @param  list<array{quote: string, title: string, role: string|null}>  $testimonials
     */
    private function graphicDesignStatsAndReviews(DOMDocument $document, DOMElement $output, array $groups, array $testimonials): void
    {
        $stats = null;
        foreach ($groups as $group) {
            if (str_contains(' '.$group->getAttribute('class').' ', ' service-section--stats ')) {
                $stats = $group;
                break;
            }
        }
        if (! $stats) {
            return;
        }

        $xpath = new DOMXPath($document);
        $copy = $xpath->query(
            './div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]',
            $stats,
        )->item(0);
        if (! $copy instanceof DOMElement) {
            return;
        }

        $metricLabels = [
            '37%' => 'Average increase in sales for our clients',
            '100%' => 'Google and Facebook-certified team',
            '81%' => 'Results improved compared to previous agencies',
        ];
        $statistics = $document->createElement('div');
        $statistics->setAttribute('class', 'graphic-design-statistics');
        $metricNodes = [];
        foreach ($metricLabels as $value => $label) {
            $metricHeading = null;
            foreach ($copy->getElementsByTagName('h2') as $candidate) {
                if (trim($candidate->textContent) === $value) {
                    $metricHeading = $candidate;
                    break;
                }
            }
            if (! $metricHeading instanceof DOMElement) {
                continue;
            }

            $card = $document->createElement('article');
            $card->setAttribute('class', 'graphic-design-statistic');
            $metricHeading->setAttribute('class', 'graphic-design-statistic__value');
            $card->appendChild($metricHeading);
            $next = $metricHeading->nextSibling;
            while ($next && $next->nodeType === XML_TEXT_NODE && trim($next->textContent) === '') {
                $next = $next->nextSibling;
            }
            if ($next instanceof DOMElement && strtolower($next->tagName) === 'p') {
                $next->setAttribute('class', 'graphic-design-statistic__label');
                $card->appendChild($next);
            } else {
                $description = $document->createElement('p');
                $description->setAttribute('class', 'graphic-design-statistic__label');
                $description->textContent = $label;
                $card->appendChild($description);
            }
            $metricNodes[] = $card;
        }

        if ($metricNodes !== []) {
            $title = $copy->getElementsByTagName('h4')->item(0);
            $firstMetric = $copy->getElementsByTagName('h2')->item(0);
            $copy->insertBefore($statistics, $firstMetric ?? $title?->nextSibling);
            foreach ($metricNodes as $card) {
                $statistics->appendChild($card);
            }
        }

        $leadHeading = null;
        foreach ($copy->getElementsByTagName('h3') as $candidate) {
            if (str_starts_with(trim($candidate->textContent), '282,000+')) {
                $leadHeading = $candidate;
                break;
            }
        }
        if ($leadHeading instanceof DOMElement) {
            $lead = $document->createElement('article');
            $lead->setAttribute('class', 'graphic-design-statistic graphic-design-statistic--lead');
            $leadHeading->setAttribute('class', 'graphic-design-statistic__lead-value');
            $lead->appendChild($leadHeading);
            foreach (iterator_to_array($copy->childNodes) as $child) {
                if ($child instanceof DOMElement
                    && (($child->tagName === 'p' && str_contains(strtolower($child->textContent), 'leads generated'))
                        || ($child->tagName === 'a' && str_contains(strtolower($child->textContent), 'contact us')))) {
                    $lead->appendChild($child);
                }
            }
            $statistics->appendChild($lead);
        }

        if ($testimonials === []) {
            return;
        }

        $reviews = $document->createElement('section');
        $reviews->setAttribute('class', 'graphic-design-reviews');
        $reviews->setAttribute('aria-label', 'Client reviews');
        $quoteColumn = $document->createElement('div');
        $quoteColumn->setAttribute('class', 'graphic-design-reviews__quote');
        $cards = $document->createElement('div');
        $cards->setAttribute('class', 'home-results__testimonials');
        $cards->setAttribute('aria-label', 'Customer testimonials');
        foreach ($testimonials as $index => $testimonial) {
            $quote = trim($testimonial['quote'] ?? '');
            if ($quote === '') {
                continue;
            }

            $card = $document->createElement('blockquote');
            $card->setAttribute('class', 'home-results__testimonial');
            if ($index > 0) {
                $card->setAttribute('hidden', '');
            }
            $text = $document->createElement('p');
            $text->appendChild($document->createTextNode($quote));
            $card->appendChild($text);
            $footer = $document->createElement('footer');
            $author = $document->createElement('cite');
            $author->appendChild($document->createTextNode($testimonial['title']));
            $footer->appendChild($author);
            if (filled($testimonial['role'])) {
                $role = $document->createElement('span');
                $role->appendChild($document->createTextNode($testimonial['role']));
                $footer->appendChild($role);
            }
            $card->appendChild($footer);
            $cards->appendChild($card);
        }
        if (! $cards->hasChildNodes()) {
            return;
        }
        $quoteColumn->appendChild($cards);

        $proofColumn = $document->createElement('div');
        $proofColumn->setAttribute('class', 'graphic-design-reviews__proof');
        foreach (iterator_to_array($copy->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            $isRating = $child->tagName === 'ul' && $child->getElementsByTagName('li')->length >= 5;
            $isReviewCount = $child->tagName === 'p' && str_contains(strtolower($child->textContent), 'client reviews');
            $isReviewLink = $child->tagName === 'a' && str_contains(strtolower($child->textContent), 'view all reviews');
            if ($isRating || $isReviewCount || $isReviewLink) {
                $proofColumn->appendChild($child);
            }
        }

        $media = $xpath->query(
            './div[contains(concat(" ", normalize-space(@class), " "), " service-media ")]',
            $stats,
        )->item(0);
        if ($media instanceof DOMElement) {
            $avatars = $document->createElement('div');
            $avatars->setAttribute('class', 'graphic-design-reviews__avatars');
            $avatars->setAttribute('aria-hidden', 'true');
            foreach (iterator_to_array($media->getElementsByTagName('img')) as $image) {
                if (str_ends_with(strtolower($image->getAttribute('src')), '.svg')) {
                    $image->setAttribute('class', 'graphic-design-reviews__arrow');
                    $image->setAttribute('aria-hidden', 'true');
                    $quoteColumn->appendChild($image);
                } else {
                    $avatars->appendChild($image);
                }
            }
            if ($avatars->hasChildNodes()) {
                $proofColumn->appendChild($avatars);
            }
            $stats->removeChild($media);
            $classes = array_values(array_filter(
                explode(' ', $stats->getAttribute('class')),
                fn (string $class): bool => $class !== 'service-section--illustrated',
            ));
            $stats->setAttribute('class', implode(' ', $classes));
        }

        $reviews->appendChild($quoteColumn);
        $reviews->appendChild($proofColumn);
        $output->insertBefore($reviews, $stats->nextSibling);
    }

    private function locationDetails(DOMDocument $document, DOMElement $copy): void
    {
        $details = null;
        foreach (iterator_to_array($copy->childNodes) as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'summary') {
                $details = $document->createElement('details');
                $details->setAttribute('class', 'location-disclosure');
                $copy->insertBefore($details, $child);
                $details->appendChild($child);
            } elseif ($details) {
                $details->appendChild($child);
            }
        }
    }

    private function performanceMarketingAgencyLayout(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $inServices = false;

        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $headings = $this->directHeadings($copy);
            $labels = array_map(fn (DOMElement $heading): string => strtolower(trim($heading->textContent)), $headings);
            $label = $labels[0] ?? '';

            if (str_starts_with($label, 'our performance marketing services')) {
                $inServices = true;
                $this->addClasses($group, ['service-section--performance-services-heading'], ['service-section--text']);
            } elseif (str_starts_with($label, 'industries we serve')) {
                $inServices = false;
            } elseif ($inServices && preg_match('/^\d+\s*\./', $label)) {
                $this->addClasses($group, ['service-section--performance-service'], ['service-section--text']);
            }

            if ($index === 0) {
                $this->addClasses($group, ['service-section--performance-hero'], ['service-section--text']);
                $group->appendChild($this->performanceMarketingForm($document, 'performance-hero'));
            }
            if (str_starts_with($label, 'what is performance marketing')) {
                $this->addClasses($group, ['service-section--performance-definition']);
            }
            if (array_filter($labels, fn (string $heading): bool => str_starts_with($heading, 'why trivo is the right performance marketing partner'))) {
                $this->addClasses($group, ['service-section--performance-partner'], ['service-section--text']);
            }
            if (str_starts_with($label, 'our performance process')) {
                $this->addClasses($group, ['service-section--performance-process'], ['service-section--text']);
            }
            if (str_starts_with($label, 'tools & platforms we use')) {
                $this->addClasses($group, ['service-section--performance-tools'], ['service-section--text']);
            }
            if (str_starts_with($label, 'ready to grow with a result-driven performance marketing agency')) {
                $this->addClasses($group, ['service-section--performance-cta'], ['service-section--text']);
            }
            if (str_starts_with($label, 'frequently asked questions')) {
                $this->addClasses($group, ['service-section--performance-faq']);
            }
            if (str_starts_with($label, 'get in touch')) {
                $this->addClasses($group, ['service-section--performance-contact'], ['service-section--text']);
                $group->appendChild($this->performanceMarketingForm($document, 'performance-contact'));
            }

            $clientReviews = null;
            foreach ($headings as $heading) {
                if ($heading->tagName === 'h3' && strtolower(trim($heading->textContent)) === 'client reviews') {
                    $clientReviews = $heading;
                    break;
                }
            }
            if ($clientReviews) {
                $copy->removeChild($clientReviews);
                $proof = $document->createElement('section');
                $proof->setAttribute('class', 'performance-marketing-proof');
                $proof->setAttribute('aria-label', 'Client reviews and results');
                $proof->appendChild($clientReviews);

                $metrics = $document->createElement('div');
                $metrics->setAttribute('class', 'performance-marketing-proof__metrics');
                foreach ([
                    ['0+', 'Client reviews'],
                    ['0+', 'Client'],
                    ['0', 'Clutch rating'],
                ] as [$value, $label]) {
                    $metric = $document->createElement('article');
                    $metric->setAttribute('class', 'performance-marketing-proof__metric');
                    $metricValue = $document->createElement('strong', $value);
                    $metricLabel = $document->createElement('span', $label);
                    $metric->appendChild($metricValue);
                    $metric->appendChild($metricLabel);
                    $metrics->appendChild($metric);
                }
                $proof->appendChild($metrics);
                $output->insertBefore($proof, $group);
                $this->addClasses($group, ['service-section--performance-partner'], ['service-section--text']);
            }
        }

        $this->performanceMarketingBenefitCards($document, $output, $groups);
        $this->performanceMarketingIndustries($document, $output, $groups);
        $this->performanceMarketingToolCards($document, $groups, $xpath);
    }

    private function performanceMarketingBenefitCards(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $start = null;
        $end = null;
        $xpath = new DOMXPath($document);

        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            foreach ($this->directHeadings($copy) as $heading) {
                $label = strtolower(trim($heading->textContent));
                if (str_contains($label, 'why hundreds of businesses choose us')) {
                    $start = $group;
                }
                if (str_starts_with($label, 'our performance marketing services')) {
                    $end = $group;
                }
            }
        }
        if (! $start || ! $end) {
            return;
        }

        $section = $document->createElement('section');
        $section->setAttribute('class', 'performance-marketing-benefits');
        $grid = $document->createElement('div');
        $grid->setAttribute('class', 'performance-marketing-benefits__grid');
        $activeCard = null;
        $started = false;

        foreach ($groups as $group) {
            if ($group === $end) {
                break;
            }
            if (! $started && $group !== $start) {
                continue;
            }
            $started = true;
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            foreach (iterator_to_array($copy->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                $label = strtolower(trim($child->textContent));
                if (in_array($child->tagName, ['h2', 'h3', 'h4'], true)
                    && str_contains($label, 'why hundreds of businesses choose us')) {
                    $section->appendChild($child);
                    $activeCard = null;
                    continue;
                }
                if (in_array($child->tagName, ['h2', 'h3', 'h4'], true) && preg_match('/^\d+\s*\./', $label)) {
                    $activeCard = $document->createElement('article');
                    $activeCard->setAttribute('class', 'performance-marketing-benefit');
                    $cardHeading = $document->createElement('h3');
                    while ($child->firstChild) {
                        $cardHeading->appendChild($child->firstChild);
                    }
                    $activeCard->appendChild($cardHeading);
                    $grid->appendChild($activeCard);
                    continue;
                }
                if ($activeCard) {
                    $activeCard->appendChild($child);
                } else {
                    $section->appendChild($child);
                }
            }
            $output->removeChild($group);
        }

        $section->appendChild($grid);
        $output->insertBefore($section, $end);
    }

    private function performanceMarketingIndustries(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $start = null;
        $end = null;

        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            $heading = $this->firstHeading($copy);
            $label = strtolower(trim($heading?->textContent ?? ''));
            if (str_starts_with($label, 'industries we serve')) {
                $start = $group;
            }
            if (str_starts_with($label, 'our performance process')) {
                $end = $group;
            }
        }
        if (! $start || ! $end) {
            return;
        }

        $section = $document->createElement('section');
        $section->setAttribute('class', 'performance-marketing-industries');
        $grid = $document->createElement('div');
        $grid->setAttribute('class', 'performance-marketing-industries__grid');
        $pendingFigure = null;
        $pendingTitle = null;
        $cardsStarted = false;
        $started = false;

        foreach ($groups as $group) {
            if ($group === $end) {
                break;
            }
            if (! $started && $group !== $start) {
                continue;
            }
            $started = true;
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            foreach (iterator_to_array($copy->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                $label = strtolower(trim($child->textContent));
                if ($child->tagName === 'h4' && str_starts_with($label, 'industries we serve')) {
                    $heading = $document->createElement('h2');
                    while ($child->firstChild) {
                        $heading->appendChild($child->firstChild);
                    }
                    $section->appendChild($heading);
                } elseif ($child->tagName === 'figure') {
                    $cardsStarted = true;
                    if ($pendingTitle) {
                        $this->appendPerformanceIndustry($document, $grid, $child, $pendingTitle);
                        $pendingTitle = null;
                    } else {
                        $pendingFigure = $child;
                    }
                } elseif ($child->tagName === 'h6') {
                    $cardsStarted = true;
                    if ($pendingFigure) {
                        $this->appendPerformanceIndustry($document, $grid, $pendingFigure, $child);
                        $pendingFigure = null;
                    } else {
                        $pendingTitle = $child;
                    }
                } elseif ($child->tagName === 'p' && $cardsStarted) {
                    $section->appendChild($grid);
                    $section->appendChild($child);
                    $grid = $document->createElement('div');
                    $grid->setAttribute('class', 'performance-marketing-industries__grid');
                    $cardsStarted = false;
                } else {
                    $section->appendChild($child);
                }
            }
            $output->removeChild($group);
        }

        if ($pendingFigure && $pendingTitle) {
            $this->appendPerformanceIndustry($document, $grid, $pendingFigure, $pendingTitle);
        }
        if ($grid->hasChildNodes()) {
            $section->appendChild($grid);
        }
        $output->insertBefore($section, $end);
    }

    private function appendPerformanceIndustry(
        DOMDocument $document,
        DOMElement $grid,
        DOMElement $figure,
        DOMElement $title,
    ): void {
        $card = $document->createElement('article');
        $card->setAttribute('class', 'performance-marketing-industry');
        $card->appendChild($figure);
        $heading = $document->createElement('h3');
        while ($title->firstChild) {
            $heading->appendChild($title->firstChild);
        }
        $card->appendChild($heading);
        $grid->appendChild($card);
    }

    private function performanceMarketingToolCards(DOMDocument $document, array $groups, DOMXPath $xpath): void
    {
        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy || ! str_starts_with(strtolower(trim($this->firstHeading($copy)?->textContent ?? '')), 'tools & platforms we use')) {
                continue;
            }

            $grid = $document->createElement('div');
            $grid->setAttribute('class', 'performance-marketing-tools__grid');
            $pendingFigure = null;
            foreach (iterator_to_array($copy->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child->tagName === 'figure') {
                    $pendingFigure = $child;
                    continue;
                }
                if ($child->tagName !== 'h5' || ! $pendingFigure) {
                    continue;
                }

                $card = $document->createElement('article');
                $card->setAttribute('class', 'performance-marketing-tool');
                $card->appendChild($pendingFigure);
                $pendingFigure = null;
                $heading = $document->createElement('h3');
                while ($child->firstChild) {
                    $heading->appendChild($child->firstChild);
                }
                $card->appendChild($heading);
                $grid->appendChild($card);
            }
            $copy->appendChild($grid);
            break;
        }
    }

    private function performanceMarketingForm(DOMDocument $document, string $prefix): DOMElement
    {
        $form = $document->createElement('form');
        $form->setAttribute('class', 'performance-marketing-form');
        $form->setAttribute('data-home-enquiry', '');
        $form->setAttribute('aria-label', 'Contact Trivo');
        $form->setAttribute('action', 'mailto:contact@trivo.in');
        $form->setAttribute('method', 'post');
        $form->setAttribute('enctype', 'text/plain');

        foreach ([
            ['name', 'Name', 'text', 'name'],
            ['email', 'Email', 'email', 'email'],
            ['phone', 'Phone number', 'tel', 'tel'],
            ['message', 'How can we help you?', 'textarea', 'off'],
        ] as [$name, $placeholder, $type, $autocomplete]) {
            $id = $prefix.'-'.$name;
            $label = $document->createElement('label', $placeholder);
            $label->setAttribute('class', 'sr-only');
            $label->setAttribute('for', $id);
            $form->appendChild($label);

            $field = $type === 'textarea'
                ? $document->createElement('textarea')
                : $document->createElement('input');
            $field->setAttribute('id', $id);
            $field->setAttribute('name', $name);
            $field->setAttribute('placeholder', $placeholder);
            $field->setAttribute('aria-label', $placeholder);
            if ($type === 'textarea') {
                $field->setAttribute('rows', '3');
            } else {
                $field->setAttribute('type', $type);
                $field->setAttribute('autocomplete', $autocomplete);
            }
            if ($name !== 'message') {
                $field->setAttribute('required', '');
            }
            $form->appendChild($field);
        }

        $company = $document->createElement('input');
        $company->setAttribute('type', 'hidden');
        $company->setAttribute('name', 'company');
        $company->setAttribute('value', '');
        $form->appendChild($company);

        $button = $document->createElement('button', 'Send me a proposal');
        $button->setAttribute('type', 'submit');
        $arrow = $document->createElement('span', '→');
        $arrow->setAttribute('aria-hidden', 'true');
        $button->appendChild($arrow);
        $form->appendChild($button);

        return $form;
    }

    private function serviceCopy(DOMXPath $xpath, DOMElement $group): ?DOMElement
    {
        $copy = $xpath->query('./div[contains(concat(" ", normalize-space(@class), " "), " service-copy ")]', $group)->item(0);

        return $copy instanceof DOMElement ? $copy : null;
    }

    /** @return list<DOMElement> */
    private function directHeadings(DOMElement $copy): array
    {
        return array_values(array_filter(
            iterator_to_array($copy->childNodes),
            fn ($child): bool => $child instanceof DOMElement && preg_match('/^h[1-6]$/', $child->tagName) === 1,
        ));
    }

    private function firstHeading(DOMElement $copy): ?DOMElement
    {
        return $this->directHeadings($copy)[0] ?? null;
    }

    /** @param list<string> $add @param list<string> $remove */
    private function addClasses(DOMElement $element, array $add, array $remove = []): void
    {
        $classes = array_filter(preg_split('/\s+/', trim($element->getAttribute('class'))) ?: []);
        $classes = array_values(array_diff($classes, $remove));
        foreach ($add as $class) {
            if (! in_array($class, $classes, true)) {
                $classes[] = $class;
            }
        }
        $element->setAttribute('class', implode(' ', $classes));
    }

    private function featureCards(DOMDocument $document, DOMElement $copy): void
    {
        $cards = null;
        $card = null;
        foreach (iterator_to_array($copy->childNodes) as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'h5') {
                if (! $cards) {
                    $cards = $document->createElement('div');
                    $cards->setAttribute('class', 'service-feature-cards');
                    $copy->insertBefore($cards, $child);
                }
                $card = $document->createElement('article');
                $card->setAttribute('class', 'service-feature-card');
                $cards->appendChild($card);
            }
            if ($card) {
                $card->appendChild($child);
            }
        }
    }

    private function seoServicePoints(DOMDocument $document, DOMElement $copy): void
    {
        $heading = $copy->getElementsByTagName('h3')->item(0);
        if (! $heading || trim($heading->textContent) !== 'Our Complete SEO Services') {
            return;
        }

        $cards = (new DOMXPath($document))->query(
            './div[contains(concat(" ", normalize-space(@class), " "), " service-feature-cards ")]',
            $copy,
        )->item(0);
        if (! $cards instanceof DOMElement) {
            return;
        }

        $hasDescription = false;
        foreach ($copy->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'p') {
                $hasDescription = true;
                break;
            }
        }
        if (! $hasDescription) {
            $description = $document->createElement('p');
            $description->setAttribute('class', 'service-section__description');
            $description->textContent = 'We offer a full range of SEO services designed to improve your website’s visibility, drive organic traffic, and increase conversions. Our strategies are tailored to your business goals and continuously optimized for better results.';
            $copy->insertBefore($description, $cards);
        }

        $pointsByService = [
            'On-Page SEO' => [
                'Keyword Research & Optimization',
                'SEO-Friendly Content Creation',
                'Meta Tags, Headers & URL Structuring',
                'Image Optimization & Internal Linking',
            ],
            'Off-Page SEO' => [
                'High-Quality Link Building',
                'Guest Blogging & Outreach',
                'Social Media & Brand Mentions',
                'Local SEO & Google My Business Optimization',
            ],
            'Technical SEO' => [
                'Website Speed & Mobile Optimization',
                'Fixing Broken Links & Redirects',
                'Schema Markup Implementation',
                'Secure HTTPS & Core Web Vitals Optimization',
            ],
        ];

        foreach ($cards->getElementsByTagName('article') as $card) {
            $serviceHeading = $card->getElementsByTagName('h5')->item(0);
            $points = $pointsByService[trim($serviceHeading?->textContent ?? '')] ?? null;
            if (! $points || $card->getElementsByTagName('ul')->length > 0) {
                continue;
            }

            $list = $document->createElement('ul');
            $list->setAttribute('class', 'service-feature-points');
            foreach ($points as $point) {
                $item = $document->createElement('li');
                $pin = $document->createElement('span', '📌');
                $pin->setAttribute('class', 'service-feature-point__pin');
                $pin->setAttribute('aria-hidden', 'true');
                $item->appendChild($pin);
                $item->appendChild($document->createTextNode($point));
                $list->appendChild($item);
            }
            $card->appendChild($list);
        }
    }

    /** Build accessible tabs using the headings and copy already stored on the page. */
    private function tabs(DOMDocument $document, array $groups, array $labels): void
    {
        $matched = array_values(array_filter($groups, function (DOMElement $group) use ($labels): bool {
            $heading = (new DOMXPath($group->ownerDocument))->query('./div/*[self::h3 or self::h4]', $group)->item(0);
            return $heading && in_array(trim($heading->textContent), $labels, true);
        }));
        if (count($matched) < 2) {
            return;
        }
        $tabs = $document->createElement('div');
        $tabs->setAttribute('class', 'home-service-tabs service-tabs');
        $list = $document->createElement('div');
        $list->setAttribute('class', 'home-service-tabs__list');
        $list->setAttribute('role', 'tablist');
        $list->setAttribute('aria-label', 'Service options');
        $tabs->appendChild($list);
        $matched[0]->parentNode->insertBefore($tabs, $matched[0]);
        foreach ($matched as $index => $group) {
            $heading = (new DOMXPath($document))->query('./div/*[self::h3 or self::h4]', $group)->item(0);
            $button = $document->createElement('button');
            $button->textContent = trim($heading->textContent);
            $button->setAttribute('class', 'home-service-tabs__tab');
            $button->setAttribute('type', 'button');
            $button->setAttribute('role', 'tab');
            $button->setAttribute('id', 'service-tab-'.$index);
            $button->setAttribute('aria-controls', 'service-panel-'.$index);
            $button->setAttribute('aria-selected', $index === 0 ? 'true' : 'false');
            $button->setAttribute('tabindex', $index === 0 ? '0' : '-1');
            $list->appendChild($button);
            $group->setAttribute('class', 'home-service-tabs__panel');
            $group->setAttribute('role', 'tabpanel');
            $group->setAttribute('id', 'service-panel-'.$index);
            $group->setAttribute('aria-labelledby', 'service-tab-'.$index);
            if ($index > 0) {
                $group->setAttribute('hidden', '');
            }
            $tabs->appendChild($group);
        }
    }
}
