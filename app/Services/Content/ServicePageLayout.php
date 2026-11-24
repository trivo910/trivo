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
            $layout = app(PaidSearchPageLayout::class)->render($html);

            return app(ClientLogoMarquee::class)->transform($layout);
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
        } elseif ($path === '/seo-company-surat') {
            $this->seoCompanySuratLayout($document, $output, $groups);
        } elseif ($path === '/seo-company-mumbai') {
            $this->seoCompanyMumbaiLayout($document, $output, $groups);
        } elseif ($path === '/digital-marketing-agency-in-mumbai') {
            $this->digitalMarketingMumbaiLayout($document, $output, $groups);
        } elseif ($path === '/social-media-marketing-agency-in-mumbai') {
            $this->socialMediaMumbaiLayout($document, $output, $groups);
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

        return app(ClientLogoMarquee::class)->transform($document->saveHTML($output));
    }

    private function socialMediaMumbaiLayout(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $metrics = [];
        $benefits = null;
        $clients = $xpath->query('./section[contains(@class,"paid-search-clients")]', $output)->item(0);
        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            $heading = $this->firstHeading($copy);
            $label = strtolower(trim($heading?->textContent ?? ''));
            if ($index === 0) {
                $this->addClasses($group, ['sm-hero']);
                foreach ($copy->getElementsByTagName('a') as $link) {
                    $link->setAttribute('href', '#social-media-audit');
                }
            }
            if (! $heading && str_contains(strtolower($copy->textContent), 'trusted by clients')) {
                $this->addClasses($group, ['sm-rating']);
            }
            if ($label === 'about') {
                $this->addClasses($group, ['sm-about']);
                $paragraphs = $copy->getElementsByTagName('p');
                $last = $paragraphs->item($paragraphs->length - 1);
                if ($last instanceof DOMElement && str_starts_with(trim($last->textContent), 'Chatting first')) {
                    $note = $document->createElement('section');
                    $note->setAttribute('class', 'sm-about-note');
                    $note->appendChild($last);
                    $output->insertBefore($note, $group->nextSibling);
                }
            }
            if (in_array($label, ['10+', '400+', '10m+'], true)) {
                $metrics[] = $group;
            }
            if (str_starts_with($label, 'key benefits of social media')) {
                $benefits = $group;
                $this->addClasses($group, ['sm-benefits-intro']);
            }
            if ($heading?->tagName === 'h4') {
                $this->addClasses($group, ['sm-prose']);
            }
            if ($label === 'our social media marketing process') {
                $this->addClasses($group, ['sm-process-intro']);
            }
            if ($label === 'tools we use') {
                $this->addClasses($group, ['sm-tools']);
            }
            if (str_starts_with($label, 'industries we support')) {
                $this->addClasses($group, ['sm-industries']);
            }
            if (str_starts_with($label, "why we're a good fit")) {
                $this->addClasses($group, ['sm-why']);
                $fit = $document->createElement('div');
                $fit->setAttribute('class', 'sm-fit-grid');
                foreach (iterator_to_array($copy->getElementsByTagName('h6')) as $item) {
                    $fit->appendChild($item);
                }
                $copy->insertBefore($fit, $heading->nextSibling);
                $why = $copy->getElementsByTagName('h2')->item(0);
                $why?->setAttribute('class', 'sm-why-heading');
            }
            if (str_starts_with($label, 'frequently asked')) {
                $this->addClasses($group, ['sm-faq']);
                $copy->getElementsByTagName('details')->item(0)?->setAttribute('open', '');
            }
            if ($label === 'social media (free audit)') {
                $this->addClasses($group, ['sm-audit']);
                $group->setAttribute('id', 'social-media-audit');
                $fragment = new DOMDocument();
                @$fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.view('partials.digital-mumbai-audit', ['auditPrefix' => 'sm'])->render().'</body></html>');
                foreach (iterator_to_array($fragment->getElementsByTagName('body')->item(0)->childNodes) as $child) {
                    $copy->appendChild($document->importNode($child, true));
                }
            }
        }
        if ($benefits) {
            $stats = $document->createElement('section');
            $stats->setAttribute('class', 'sm-stats');
            foreach ([['10+', 'Years of Industry Experience'], ['400+', 'Projects Delivered Across Multiple Domains'], ['10M+', 'Strong Client Satisfaction Record'], ['', 'Google Certified Practitioner'], ['', 'Meta Certified Practitioner'], ['', '100% Online Brand Visibility']] as [$number, $label]) {
                $card = $document->createElement('div');
                $icon = $document->createElement('span', '✓');
                $icon->setAttribute('aria-hidden', 'true');
                $card->appendChild($icon);
                $card->appendChild($document->createElement('strong', $number));
                $description = $document->createElement('p');
                $description->appendChild($document->createTextNode($label));
                $card->appendChild($description);
                $stats->appendChild($card);
            }
            $output->insertBefore($stats, $benefits);
            foreach ($metrics as $metric) {
                $metric->parentNode?->removeChild($metric);
            }
        }
        if ($clients instanceof DOMElement) {
            $this->addClasses($clients, ['sm-clients']);
            foreach (iterator_to_array($clients->getElementsByTagName('img')) as $image) {
                if (str_ends_with($image->getAttribute('src'), '/download.png')) {
                    $figure = $image->parentNode;
                    if ($figure instanceof DOMElement && $figure->tagName === 'figure') {
                        $figure->parentNode?->removeChild($figure);
                    } else {
                        $image->parentNode?->removeChild($image);
                    }
                }
            }
        }
        foreach ($xpath->query('.//h2|.//h3', $output) as $heading) {
            $text = trim($heading->textContent);
            $accent = match (true) {
                str_starts_with($text, 'Key Benefits') => 'Social Media',
                $text === 'Marketing' => 'Marketing',
                str_contains($text, 'Marketing Process') => 'Process',
                $text === 'Tools We Use' => 'Tools',
                str_contains($text, 'Why choose The Trivo') => 'The Trivo',
                str_contains($text, 'Asked Questions') => 'Questions',
                default => null,
            };
            if (! $accent) {
                continue;
            }
            [$before, $after] = explode($accent, $text, 2);
            while ($heading->firstChild) {
                $heading->removeChild($heading->firstChild);
            }
            $heading->appendChild($document->createTextNode($before));
            $span = $document->createElement('span');
            $span->setAttribute('class', 'sm-accent');
            $span->appendChild($document->createTextNode($accent));
            $heading->appendChild($span);
            $heading->appendChild($document->createTextNode($after));
        }
    }

    private function digitalMarketingMumbaiLayout(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $services = [];
        $serviceIntro = null;
        $process = null;
        $processActive = false;
        $metrics = [];
        $clients = $xpath->query('./section[contains(@class,"paid-search-clients")]', $output)->item(0);
        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            $heading = $this->firstHeading($copy);
            $label = strtolower(trim($heading?->textContent ?? ''));
            if ($index === 0) {
                $this->addClasses($group, ['dm-hero']);
            }
            if (str_starts_with($label, 'elevate your business')) {
                $this->addClasses($group, ['dm-about']);
            }
            if (in_array($label, ['10+', '400+', '10m+'], true)) {
                $metrics[] = $group;
            }
            if ($label === 'modern tools & techniques') {
                $serviceIntro = $group;
                $this->addClasses($group, ['dm-services-intro']);
                foreach (iterator_to_array($copy->getElementsByTagName('h6')) as $smallHeading) {
                    $copy->removeChild($smallHeading);
                }
            }
            if (in_array($label, ['pay-per-click advertising (ppc)', 'social media marketing (smm)', 'search engine optimization (seo)'], true)) {
                $services[] = $group;
                $this->addClasses($group, ['dm-service-card']);
                $link = $copy->getElementsByTagName('a')->item(0);
                $link?->setAttribute('href', url(match (count($services)) {
                    1 => '/paid-search-marketing', 2 => '/social-media-marketing', default => '/search-engine-optimization',
                }));
            }
            if (str_starts_with($label, 'tools & platforms')) {
                $this->addClasses($group, ['dm-tools']);
            } elseif (str_starts_with($label, 'key benefits')) {
                $this->addClasses($group, ['dm-benefits']);
            } elseif (str_starts_with($label, 'industries we support')) {
                $this->addClasses($group, ['dm-industries']);
            } elseif ($label === 'our digital marketing process') {
                $process = $group;
                $processActive = true;
                $this->addClasses($group, ['dm-process']);
            } elseif ($processActive && str_starts_with($label, 'why businesses choose')) {
                $processActive = false;
            } elseif ($processActive && $process) {
                $target = $this->serviceCopy($xpath, $process);
                foreach (iterator_to_array($copy->childNodes) as $child) {
                    $target->appendChild($child);
                }
                $group->parentNode?->removeChild($group);
            }
            if (str_starts_with($label, 'why businesses choose')) {
                $this->addClasses($group, [$copy->getElementsByTagName('h6')->length ? 'dm-reasons' : 'dm-capabilities']);
            }
            if (str_starts_with($label, 'our digital marketing capabilities')) {
                $this->addClasses($group, ['dm-capabilities-list']);
            }
            if (str_starts_with($label, 'frequently asked')) {
                $this->addClasses($group, ['dm-faq']);
                $copy->getElementsByTagName('details')->item(0)?->setAttribute('open', '');
            }
            if (str_starts_with($label, 'professional digital marketing audit')) {
                $this->addClasses($group, ['dm-audit']);
                $fragment = new DOMDocument();
                @$fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.view('partials.digital-mumbai-audit')->render().'</body></html>');
                foreach (iterator_to_array($fragment->getElementsByTagName('body')->item(0)->childNodes) as $child) {
                    $copy->appendChild($document->importNode($child, true));
                }
            }
            if (! $heading && str_contains(strtolower($copy->textContent), 'trusted by clients')) {
                $this->addClasses($group, ['dm-google-rating']);
                if ($clients instanceof DOMElement && $clients->nextSibling !== $group) {
                    $clients->parentNode?->insertBefore($group, $clients->nextSibling);
                }
            }
        }
        if ($serviceIntro) {
            $stats = $document->createElement('section');
            $stats->setAttribute('class', 'dm-stats');
            foreach ([['10+', 'Client-Focused Approach'], ['400+', 'Experienced Digital Specialists'], ['10M+', 'Transparent Communication'], ['', 'Modern Tools & Techniques'], ['', 'Timely Execution'], ['', 'Reliable Support']] as [$number, $text]) {
                $card = $document->createElement('div');
                $icon = $document->createElement('span', '✓');
                $icon->setAttribute('aria-hidden', 'true');
                $card->appendChild($icon);
                $card->appendChild($document->createElement('strong', $number));
                $paragraph = $document->createElement('p');
                $paragraph->appendChild($document->createTextNode($text));
                $card->appendChild($paragraph);
                $stats->appendChild($card);
            }
            $output->insertBefore($stats, $serviceIntro);
            foreach ($metrics as $metric) {
                $metric->parentNode?->removeChild($metric);
            }
            // Each imported service illustration occurs before its corresponding heading.
            $figures = [];
            foreach ([$serviceIntro, ...$services] as $source) {
                $copy = $this->serviceCopy($xpath, $source);
                foreach (iterator_to_array($xpath->query('./figure', $copy)) as $figure) {
                    $figures[] = $figure;
                    $copy->removeChild($figure);
                }
            }
            foreach ($services as $index => $service) {
                $copy = $this->serviceCopy($xpath, $service);
                if (isset($figures[$index])) {
                    $copy->insertBefore($figures[$index], $copy->firstChild);
                }
            }
        }
        if ($clients instanceof DOMElement) {
            $this->addClasses($clients, ['dm-clients']);
            foreach (iterator_to_array($clients->getElementsByTagName('img')) as $image) {
                if (str_ends_with($image->getAttribute('src'), '/download.png')) {
                    $image->parentNode?->parentNode?->removeChild($image->parentNode);
                }
            }
        }
        foreach ($xpath->query('.//h3', $output) as $heading) {
            $text = trim($heading->textContent);
            $highlight = str_contains($text, 'Digital Marketing') ? 'Digital Marketing' : (str_contains($text, 'Asked Questions') ? 'Asked Questions' : null);
            if (! $highlight) {
                continue;
            }
            [$before, $after] = explode($highlight, $text, 2);
            while ($heading->firstChild) {
                $heading->removeChild($heading->firstChild);
            }
            $heading->appendChild($document->createTextNode($before));
            $span = $document->createElement('span');
            $span->setAttribute('class', 'dm-heading-accent');
            $span->appendChild($document->createTextNode($highlight));
            $heading->appendChild($span);
            $heading->appendChild($document->createTextNode($after));
        }
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

    private function seoCompanySuratLayout(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);

        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $headings = array_map(
                fn (DOMElement $heading): string => strtolower(trim(preg_replace('/\s+/', ' ', $heading->textContent))),
                $this->directHeadings($copy),
            );
            $label = $headings[0] ?? '';

            if ($index === 0) {
                $this->addClasses($group, ['service-section--surat-hero'], ['service-section--text']);
                $group->appendChild($this->seoCompanySuratForm($document, 'surat-hero'));
            }
            if (str_starts_with($label, 'why your business in surat needs seo')) {
                $this->addClasses($group, ['service-section--surat-needs']);
            }
            if (in_array('client reviews', $headings, true)
                || array_filter($headings, fn (string $heading): bool => str_contains($heading, 'why choose trivo as your seo partner'))) {
                $this->addClasses($group, ['service-section--surat-choose']);
            }
            if (str_starts_with($label, 'tools we use for seo success')) {
                $this->addClasses($group, ['service-section--surat-tools']);
            }
            if (str_starts_with($label, 'get started with the best seo company in surat')) {
                $this->addClasses($group, ['service-section--surat-cta']);
            }
            if (str_starts_with($label, 'request a free seo audit')) {
                $this->addClasses($group, ['service-section--surat-audit']);
                $group->appendChild($this->seoCompanySuratForm($document, 'surat-audit'));
            }
        }

        $this->suratSeoReviewsSection($document, $output, $groups, $xpath);
        $this->suratSeoServices($document, $output, $groups, $xpath);
        $this->suratSeoAudienceSections($document, $output, $groups, $xpath);
        $this->suratSeoGrowthSection($document, $output, $groups, $xpath);

        foreach ($xpath->query('./section[contains(concat(" ", normalize-space(@class), " "), " paid-search-clients ")]', $output) ?: [] as $clients) {
            $this->addClasses($clients, ['surat-seo-clients']);
        }
    }

    private function seoCompanyMumbaiLayout(DOMDocument $document, DOMElement $output, array $groups): void
    {
        $xpath = new DOMXPath($document);
        $hero = $groups[0] ?? null;
        $heroCopy = $hero instanceof DOMElement ? $this->serviceCopy($xpath, $hero) : null;
        $intro = $groups[1] ?? null;
        $introCopy = $intro instanceof DOMElement ? $this->serviceCopy($xpath, $intro) : null;

        if ($hero instanceof DOMElement && $heroCopy instanceof DOMElement) {
            foreach (iterator_to_array($heroCopy->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->tagName === 'p'
                    && str_contains(strtolower($child->textContent), 'ai - driven growth solution')) {
                    $heroCopy->removeChild($child);
                    break;
                }
            }
            $this->addClasses($hero, ['service-section--mumbai-hero'], ['service-section--text']);
        }

        if ($heroCopy instanceof DOMElement && $intro instanceof DOMElement && $introCopy instanceof DOMElement) {
            foreach (iterator_to_array($introCopy->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child->tagName === 'h2' && str_contains(strtolower($child->textContent), 'seo company in mumbai')) {
                    $introCopy->removeChild($child);
                    continue;
                }
                if ($child->tagName === 'h3' && str_contains(strtolower($child->textContent), 'applying dependable seo techniques')) {
                    $heading = $document->createElement('h2');
                    while ($child->firstChild) {
                        $heading->appendChild($child->firstChild);
                    }
                    $heroCopy->appendChild($heading);
                    $introCopy->removeChild($child);
                    continue;
                }
                if ($child->tagName === 'p' && str_contains(strtolower($child->textContent), 'implement successful, efficient seo')) {
                    $heroCopy->appendChild($child);
                    continue;
                }
                if ($child->tagName === 'div' && str_contains($child->getAttribute('class'), 'service-feature-cards')) {
                    $introCopy->removeChild($child);
                }
            }

            $form = $document->createElement('form');
            $form->setAttribute('class', 'performance-marketing-form');
            $form->setAttribute('action', url('/contact'));
            $form->setAttribute('method', 'get');
            $form->setAttribute('aria-label', 'Request a free SEO audit');
            $website = $document->createElement('input');
            $website->setAttribute('type', 'url');
            $website->setAttribute('name', 'website');
            $website->setAttribute('placeholder', 'www.website.com');
            $website->setAttribute('aria-label', 'Website URL');
            $form->appendChild($website);
            $submit = $document->createElement('button', 'GET FREE AUDIT');
            $submit->setAttribute('type', 'submit');
            $form->appendChild($submit);
            $heroCopy->appendChild($form);

            $logos = $document->createElement('div');
            $logos->setAttribute('class', 'mumbai-award-logos');
            foreach ([$intro, $groups[2] ?? null] as $source) {
                if (! $source instanceof DOMElement) {
                    continue;
                }
                $media = $xpath->query('./div[contains(@class,"service-media")]', $source)->item(0);
                if (! $media instanceof DOMElement) {
                    continue;
                }
                foreach (iterator_to_array($media->getElementsByTagName('img')) as $image) {
                    $image->setAttribute('loading', 'lazy');
                    $logos->appendChild($image);
                }
                $source->removeChild($media);
            }
            if ($logos->hasChildNodes()) {
                $heroCopy->appendChild($logos);
            }

            if ($intro->parentNode === $output) {
                $output->removeChild($intro);
            }
        }

        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $headings = array_map(
                fn (DOMElement $heading): string => strtolower(trim(preg_replace('/\\s+/', ' ', $heading->textContent))),
                $this->directHeadings($copy),
            );
            $label = $headings[0] ?? '';

            if ($group === $hero) {
                $copy->appendChild($this->mumbaiProofMetrics($document));
            }
            if (str_contains($label, 'seo services helping businesses grow in mumbai')) {
                $this->addClasses($group, ['service-section--mumbai-benefits']);
            } elseif (str_contains($label, 'seo services in mumbai')) {
                $this->addClasses($group, ['service-section--mumbai-location']);
            } elseif (str_contains($label, 'seo & digital growth services we offer')) {
                $this->addClasses($group, ['service-section--mumbai-services']);
            } elseif (str_contains($label, 'why choose our seo company in mumbai')) {
                $this->addClasses($group, ['service-section--mumbai-why']);
            } elseif (str_contains($label, 'our seo process to drive consistent growth')) {
                $this->addClasses($group, ['service-section--mumbai-process']);
            } elseif (str_contains($label, 'what our seo package includes')) {
                $this->addClasses($group, ['service-section--mumbai-packages']);
            } elseif (str_contains($label, 'faqs on seo services')) {
                $this->addClasses($group, ['service-section--mumbai-faq']);
            }

            if (str_contains($label, 'why choose our seo company in mumbai')) {
                $group->setAttribute('class', $group->getAttribute('class').' service-section--accordion');
            }
            if (str_contains($label, 'why choose our seo company in mumbai') || str_contains($label, 'faqs on seo services')) {
                $this->locationDetails($document, $copy);
                $details = $copy->getElementsByTagName('details');
                if ($details->length > 0) {
                    $details->item(0)?->setAttribute('open', '');
                }
            }
        }

        $this->mumbaiBenefitCards($document, $output, $groups, $xpath);
        $this->mumbaiLocationCards($document, $output, $groups, $xpath);
        $this->mumbaiProcessSteps($document, $groups, $xpath);

        $success = $document->createElement('section');
        $success->setAttribute('class', 'service-section mumbai-case-studies');
        $eyebrow = $document->createElement('p', 'CLIENT SUCCESS');
        $eyebrow->setAttribute('class', 'mumbai-case-studies__eyebrow');
        $success->appendChild($eyebrow);
        $success->appendChild($document->createElement('h2', 'Real Results, Real Businesses'));
        $success->appendChild($document->createElement('p', 'Our clients don’t just grow — they dominate their categories online.'));
        $cards = $document->createElement('div');
        $cards->setAttribute('class', 'mumbai-case-studies__grid');
        foreach ([
            ['Luhaif Perfumes', '2026/05/Luhaif-Thumbnail.png', '/luhaif-perfumes'],
            ['Mumbai Darshan', '2026/05/Mumbai-Thumbnail.png', '/mumbai-darshan'],
            ['Sell Sold', '2026/05/Sell-Sold-Thumbnail.png', '/sell-sold'],
        ] as [$title, $image, $url]) {
            $card = $document->createElement('a');
            $card->setAttribute('class', 'mumbai-case-studies__card');
            $card->setAttribute('href', url($url));
            $img = $document->createElement('img');
            $img->setAttribute('src', asset('wp-content/uploads/'.$image));
            $img->setAttribute('alt', $title.' case study');
            $img->setAttribute('loading', 'lazy');
            $card->appendChild($img);
            $meta = $document->createElement('span');
            $meta->appendChild($document->createElement('small', 'May 2026'));
            $meta->appendChild($document->createElement('strong', $title));
            $card->appendChild($meta);
            $cards->appendChild($card);
        }
        $success->appendChild($cards);
        $portfolio = $document->createElement('a', 'VIEW ALL  →');
        $portfolio->setAttribute('class', 'mumbai-case-studies__button');
        $portfolio->setAttribute('href', url('/portfolio'));
        $success->appendChild($portfolio);

        $clientSection = null;
        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if ($copy && str_contains(strtolower($copy->textContent), 'real results, real businesses')) {
                $clientSection = $group;
                break;
            }
        }
        if ($clientSection?->parentNode === $output) {
            $output->removeChild($clientSection);
        }

        $benefits = null;
        foreach ($groups as $group) {
            if (str_contains($group->getAttribute('class'), 'service-section--mumbai-benefits')) {
                $benefits = $group;
                break;
            }
        }
        if ($benefits?->parentNode === $output) {
            $benefitHeading = null;
            foreach ($output->childNodes as $child) {
                if ($child instanceof DOMElement && str_contains($child->getAttribute('class'), 'mumbai-benefits-heading')) {
                    $benefitHeading = $child;
                    break;
                }
            }
            $output->insertBefore($success, $benefitHeading ?? $benefits);
        } else {
            $output->appendChild($success);
        }
    }

    private function mumbaiBenefitCards(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        $first = null;
        $last = null;
        $auditLink = null;
        foreach ($groups as $group) {
            if (str_contains($group->getAttribute('class'), 'service-section--mumbai-benefits')) {
                $first = $group;
                break;
            }
        }
        if (! $first) {
            return;
        }
        $firstCopy = $this->serviceCopy($xpath, $first);
        $title = $this->firstHeading($firstCopy);
        if ($title) {
            $replacement = $document->createElement('h2');
            while ($title->firstChild) {
                $replacement->appendChild($title->firstChild);
            }
            $title->parentNode?->replaceChild($replacement, $title);
            $title = $replacement;
        }
        $headingSection = $document->createElement('section');
        $headingSection->setAttribute('class', 'mumbai-benefits-heading');
        if ($title) {
            $headingSection->appendChild($title);
        }
        $output->insertBefore($headingSection, $first);

        $withinBenefits = false;
        $benefitIndex = 0;
        foreach ($groups as $group) {
            if ($group === $first) {
                $withinBenefits = true;
            }
            if (! $withinBenefits || str_contains($group->getAttribute('class'), 'service-section--mumbai-services')) {
                if (str_contains($group->getAttribute('class'), 'service-section--mumbai-services')) {
                    break;
                }
                continue;
            }
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            $last = $group;
            $card = $document->createElement('article');
            $card->setAttribute('class', 'service-feature-card');
            $active = false;
            foreach (iterator_to_array($copy->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child === $title) {
                    continue;
                }
                if ($child->tagName === 'a' && str_contains(strtolower($child->textContent), 'free audit')) {
                    $auditLink = $child;
                    $copy->removeChild($child);
                } elseif ($child->tagName === 'h4') {
                    $title = $document->createElement('h5');
                    while ($child->firstChild) {
                        $title->appendChild($child->firstChild);
                    }
                    $card->appendChild($title);
                    $child->parentNode?->removeChild($child);
                    $active = true;
                } elseif ($child->tagName === 'p' && strcasecmp(trim($child->textContent), 'Get Location') === 0) {
                    $child->setAttribute('class', 'mumbai-location-eyebrow');
                } elseif ($child->tagName === 'p' && $active) {
                    $card->appendChild($child);
                }
            }
            if ($card->hasChildNodes()) {
                $copy->appendChild($card);
                $benefitIndex++;
                $this->addClasses($group, ['service-section--mumbai-benefit-item', 'service-section--mumbai-benefit-item-'.$benefitIndex], ['service-section--text']);
            }
        }
        if ($auditLink instanceof DOMElement && $last instanceof DOMElement && $last->parentNode === $output) {
            $cta = $document->createElement('div');
            $cta->setAttribute('class', 'mumbai-benefits-cta');
            $cta->appendChild($auditLink);
            $output->insertBefore($cta, $last->nextSibling);
        }
    }

    private function mumbaiLocationCards(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        $location = null;
        foreach ($groups as $group) {
            if (str_contains($group->getAttribute('class'), 'service-section--mumbai-location')) {
                $location = $group;
                break;
            }
        }
        if (! $location instanceof DOMElement) {
            return;
        }
        $copy = $this->serviceCopy($xpath, $location);
        if (! $copy) {
            return;
        }
        foreach ($this->directHeadings($copy) as $heading) {
            if ($heading->tagName === 'h3' && str_contains(strtolower($heading->textContent), 'seo services in mumbai')) {
                $replacement = $document->createElement('h2');
                while ($heading->firstChild) {
                    $replacement->appendChild($heading->firstChild);
                }
                $heading->parentNode?->replaceChild($replacement, $heading);
            }
        }
        foreach ($groups as $group) {
            $groupCopy = $this->serviceCopy($xpath, $group);
            if (! $groupCopy) {
                continue;
            }
            foreach (iterator_to_array($xpath->query('./p[contains(@class,"mumbai-location-eyebrow")]', $groupCopy)) as $eyebrow) {
                $groupCopy->removeChild($eyebrow);
                $copy->insertBefore($eyebrow, $copy->firstChild);
            }
        }
        $info = $document->createElement('div');
        $info->setAttribute('class', 'mumbai-location-card');
        $description = null;
        foreach (iterator_to_array($copy->childNodes) as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'p') {
                $description = $child;
                break;
            }
        }
        if ($description) {
            $copy->removeChild($description);
            $info->appendChild($description);
        }

        foreach ($groups as $group) {
            if ($group === $location || $group->parentNode !== $output) {
                continue;
            }
            $otherCopy = $this->serviceCopy($xpath, $group);
            if (! $otherCopy || ! str_contains(strtolower($otherCopy->textContent), 'phone number')) {
                continue;
            }
            foreach (iterator_to_array($otherCopy->childNodes) as $child) {
                if ($child instanceof DOMElement) {
                    $info->appendChild($child);
                }
            }
            $output->removeChild($group);
            break;
        }
        $map = $document->createElement('div');
        $map->setAttribute('class', 'mumbai-location-map');
        $map->setAttribute('aria-label', 'Map location for Trivo in Goregaon West, Mumbai');
        $copy->appendChild($info);
        $copy->appendChild($map);
    }

    private function mumbaiProcessSteps(DOMDocument $document, array $groups, DOMXPath $xpath): void
    {
        foreach ($groups as $group) {
            if (! str_contains($group->getAttribute('class'), 'service-section--mumbai-process')) {
                continue;
            }
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }
            $this->addClasses($group, [], ['service-section--accordion']);
            foreach (iterator_to_array($copy->getElementsByTagName('details')) as $details) {
                if (! $details instanceof DOMElement) {
                    continue;
                }
                $step = $document->createElement('div');
                $step->setAttribute('class', 'mumbai-process-step');
                $summary = $details->getElementsByTagName('summary')->item(0);
                if ($summary) {
                    $step->appendChild($summary);
                }
                $details->parentNode?->replaceChild($step, $details);
            }
        }
    }

    private function mumbaiProofMetrics(DOMDocument $document): DOMElement
    {
        $proof = $document->createElement('div');
        $proof->setAttribute('class', 'mumbai-proof');
        foreach ([['1.2B+', 'Organic Sessions'], ['29M+', 'Leads Generated'], ['130+', 'Case Studies'], ['50+', 'Awards & Recognitions']] as [$value, $label]) {
            $metric = $document->createElement('article');
            $metric->appendChild($document->createElement('strong', $value));
            $labelElement = $document->createElement('span');
            $labelElement->appendChild($document->createTextNode($label));
            $metric->appendChild($labelElement);
            $proof->appendChild($metric);
        }

        return $proof;
    }

    private function seoCompanySuratForm(DOMDocument $document, string $prefix): DOMElement
    {
        $panel = $document->createElement('aside');
        $panel->setAttribute('class', 'surat-seo-form-card');
        $panel->setAttribute('aria-label', 'Request a free SEO audit');

        $heading = $document->createElement('h3', 'Request a Free SEO Audit');
        $panel->appendChild($heading);

        $form = $this->performanceMarketingForm($document, $prefix);
        $form->setAttribute('class', 'surat-seo-form');
        $form->setAttribute('aria-label', 'Request a free SEO audit');
        $button = $form->getElementsByTagName('button')->item(0);
        if ($button) {
            $button->textContent = 'Get My Free SEO Audit';
        }
        $panel->appendChild($form);

        return $panel;
    }

    private function suratSeoReviewsSection(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy || $group->parentNode !== $output) {
                continue;
            }

            $reviewHeading = null;
            foreach ($this->directHeadings($copy) as $heading) {
                if ($heading->tagName === 'h3' && strtolower(trim($heading->textContent)) === 'client reviews') {
                    $reviewHeading = $heading;
                    break;
                }
            }
            if (! $reviewHeading) {
                continue;
            }

            $reviews = $document->createElement('section');
            $reviews->setAttribute('class', 'service-section surat-seo-reviews');
            $heading = $document->createElement('h2', 'Client Reviews');
            $reviews->appendChild($heading);

            $metrics = $document->createElement('div');
            $metrics->setAttribute('class', 'surat-seo-reviews__metrics');
            foreach ([
                ['4.7/5', 'Google Rating'],
                ['Local SEO', 'Focused on Surat'],
                ['Free', 'SEO Audit'],
            ] as [$value, $label]) {
                $metric = $document->createElement('article');
                $metric->setAttribute('class', 'surat-seo-reviews__metric');
                $metric->appendChild($document->createElement('strong', $value));
                $metric->appendChild($document->createElement('span', $label));
                $metrics->appendChild($metric);
            }
            $reviews->appendChild($metrics);
            $copy->removeChild($reviewHeading);
            $output->insertBefore($reviews, $group);

            return;
        }
    }

    private function suratSeoServices(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        $start = null;
        $end = null;

        foreach ($groups as $index => $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $headings = $this->directHeadings($copy);
            $labels = array_map(fn (DOMElement $heading): string => strtolower(trim($heading->textContent)), $headings);
            if ($start === null && in_array('our seo services in surat', $labels, true)
                && array_filter($headings, fn (DOMElement $heading): bool => $heading->tagName === 'h4' && preg_match('/^\s*\d+\s*\./', $heading->textContent) === 1)) {
                $start = $index;
            }
            if ($start !== null && in_array('additional seo support services', $labels, true)) {
                $end = $index;
                break;
            }
        }

        if ($start === null || $end === null) {
            return;
        }

        $services = $document->createElement('section');
        $services->setAttribute('class', 'service-section surat-seo-services');
        $serviceTitle = null;
        $grid = $document->createElement('div');
        $grid->setAttribute('class', 'surat-seo-services__grid');
        $cards = [];
        $images = [];
        $activeCard = null;
        $support = $document->createElement('section');
        $support->setAttribute('class', 'service-section surat-seo-support');
        $supportCopy = $document->createElement('div');
        $supportCopy->setAttribute('class', 'service-copy');
        $supportStarted = false;
        $removedGroups = [];

        for ($index = $start; $index <= $end; $index++) {
            $group = $groups[$index];
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            foreach (iterator_to_array($copy->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->tagName === 'h2') {
                    $label = strtolower(trim($child->textContent));
                    if ($label === 'our seo services in surat' && ! $serviceTitle) {
                        $serviceTitle = $child;
                        continue;
                    }
                    if ($label === 'additional seo support services') {
                        $supportStarted = true;
                        $supportCopy->appendChild($child);
                        $activeCard = null;
                        continue;
                    }
                }

                if ($supportStarted) {
                    $supportCopy->appendChild($child);
                    continue;
                }

                if ($child instanceof DOMElement && $child->tagName === 'h4'
                    && preg_match('/^\s*\d+\s*\./', $child->textContent) === 1) {
                    $activeCard = $document->createElement('article');
                    $activeCard->setAttribute('class', 'surat-seo-service');
                    $cardCopy = $document->createElement('div');
                    $cardCopy->setAttribute('class', 'surat-seo-service__copy');
                    $cardHeading = $document->createElement('h3');
                    while ($child->firstChild) {
                        $cardHeading->appendChild($child->firstChild);
                    }
                    $cardCopy->appendChild($cardHeading);
                    $activeCard->appendChild($cardCopy);
                    $cards[] = [$activeCard, $cardCopy, $cardHeading];
                    $grid->appendChild($activeCard);
                    continue;
                }

                if ($activeCard) {
                    $activeCard->getElementsByTagName('div')->item(0)?->appendChild($child);
                }
            }

            foreach ($xpath->query('./div[contains(concat(" ", normalize-space(@class), " "), " service-media ")]//img', $group) ?: [] as $image) {
                if ($image instanceof DOMElement) {
                    $image->setAttribute('loading', 'lazy');
                    $images[] = $image;
                }
            }
            $removedGroups[] = $group;
        }

        if ($serviceTitle) {
            $services->appendChild($serviceTitle);
        }
        $services->appendChild($grid);

        $descriptions = [
            'Research the terms your customers in Surat are searching for and build a strategy around their intent.',
            'Improve page titles, content, and internal links so your pages are useful to both visitors and search engines.',
            'Keep your website fast, secure, mobile-friendly, and easy for search engines to crawl.',
            'Improve your visibility in Surat searches with a complete Google Business Profile and consistent local listings.',
            'Build authority with relevant, trustworthy links and ethical outreach.',
            'Create useful blogs and landing pages that answer customer questions and support organic growth.',
        ];

        foreach ($cards as $index => [$card, $copy, $heading]) {
            $image = $images[$index] ?? null;
            if ($copy->getElementsByTagName('p')->length === 0 && isset($descriptions[$index])) {
                $description = $document->createElement('p', $descriptions[$index]);
                $copy->appendChild($description);
            }
            if (! $image instanceof DOMElement) {
                continue;
            }

            if (trim($image->getAttribute('alt')) === '') {
                $image->setAttribute('alt', 'SEO illustration for '.trim($heading->textContent));
            }
            $media = $document->createElement('div');
            $media->setAttribute('class', 'surat-seo-service__media');
            $media->appendChild($image);
            $card->appendChild($media);
        }

        $support->appendChild($supportCopy);
        $parent = $removedGroups[0]->parentNode ?? null;
        if ($parent === $output) {
            $output->insertBefore($services, $removedGroups[0]);
            $output->insertBefore($support, $removedGroups[0]);
            foreach ($removedGroups as $group) {
                $output->removeChild($group);
            }
        }
    }

    private function suratSeoAudienceSections(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $headings = $this->directHeadings($copy);
            $hasServiceItems = count(array_filter(
                $headings,
                fn (DOMElement $heading): bool => $heading->tagName === 'h6' && preg_match('/^\s*\d+\s*\./', $heading->textContent) === 1,
            )) > 0;
            $hasIndustries = count(array_filter(
                $headings,
                fn (DOMElement $heading): bool => $heading->tagName === 'h2' && strtolower(trim($heading->textContent)) === 'who we work with',
            )) > 0;
            if (! $hasServiceItems || ! $hasIndustries || $group->parentNode !== $output) {
                continue;
            }

            $services = $document->createElement('section');
            $services->setAttribute('class', 'service-section surat-seo-audit-services');
            $serviceCopy = $document->createElement('div');
            $serviceCopy->setAttribute('class', 'service-copy');
            $serviceGrid = $document->createElement('div');
            $serviceGrid->setAttribute('class', 'surat-seo-audit-services__grid');

            $industries = $document->createElement('section');
            $industries->setAttribute('class', 'service-section surat-seo-industries');
            $industryCopy = $document->createElement('div');
            $industryCopy->setAttribute('class', 'service-copy');
            $industryGrid = $document->createElement('div');
            $industryGrid->setAttribute('class', 'surat-seo-industries__grid');

            $inIndustries = false;
            foreach (iterator_to_array($copy->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->tagName === 'h2') {
                    if (strtolower(trim($child->textContent)) === 'who we work with') {
                        $inIndustries = true;
                        $industryCopy->appendChild($child);
                        continue;
                    }
                    if (! $inIndustries) {
                        $serviceCopy->appendChild($child);
                        continue;
                    }
                }

                if ($inIndustries) {
                    if ($child instanceof DOMElement && $child->tagName === 'p') {
                        $industryGrid->appendChild($child);
                    } else {
                        $industryCopy->appendChild($child);
                    }
                    continue;
                }

                if ($child instanceof DOMElement && $child->tagName === 'h6') {
                    $item = $document->createElement('article');
                    $item->setAttribute('class', 'surat-seo-audit-services__item');
                    $heading = $document->createElement('h3');
                    while ($child->firstChild) {
                        $heading->appendChild($child->firstChild);
                    }
                    $item->appendChild($heading);
                    $serviceGrid->appendChild($item);
                } else {
                    $serviceCopy->appendChild($child);
                }
            }

            $serviceCopy->appendChild($serviceGrid);
            $industryCopy->appendChild($industryGrid);
            $services->appendChild($serviceCopy);
            $industries->appendChild($industryCopy);
            $output->insertBefore($services, $group);
            $output->insertBefore($industries, $group);
            $output->removeChild($group);
            break;
        }
    }

    private function suratSeoGrowthSection(DOMDocument $document, DOMElement $output, array $groups, DOMXPath $xpath): void
    {
        foreach ($groups as $group) {
            $copy = $this->serviceCopy($xpath, $group);
            if (! $copy) {
                continue;
            }

            $faqHeading = null;
            foreach ($this->directHeadings($copy) as $heading) {
                if (str_starts_with(strtolower(trim($heading->textContent)), 'frequently asked questions')) {
                    $faqHeading = $heading;
                    break;
                }
            }
            if (! $faqHeading || $group->parentNode !== $output) {
                continue;
            }

            $growth = $document->createElement('section');
            $growth->setAttribute('class', 'service-section service-section--surat-growth');
            $growthCopy = $document->createElement('div');
            $growthCopy->setAttribute('class', 'service-copy');
            foreach (iterator_to_array($copy->childNodes) as $child) {
                if ($child === $faqHeading) {
                    break;
                }
                $growthCopy->appendChild($child);
            }

            $growth->appendChild($growthCopy);
            $this->addClasses($group, ['service-section--surat-faq'], ['service-section--text']);
            $output->insertBefore($growth, $group);
            break;
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
                foreach (iterator_to_array($copy->getElementsByTagName('p')) as $paragraph) {
                    if (str_starts_with(trim($paragraph->textContent), 'At Trivo, we blend')) {
                        $note = $document->createElement('section');
                        $note->setAttribute('class', 'performance-definition-note');
                        $note->appendChild($paragraph);
                        $output->insertBefore($note, $group->nextSibling);
                    }
                }
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
                $fragment = new DOMDocument('1.0', 'UTF-8');
                @$fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.view('partials.nagpur-contact-details')->render().'</body></html>');
                while ($copy->firstChild) {
                    $copy->removeChild($copy->firstChild);
                }
                foreach (iterator_to_array($fragment->getElementsByTagName('body')->item(0)->childNodes) as $child) {
                    $copy->appendChild($document->importNode($child, true));
                }
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
                    ['798+', 'Client Reviews'],
                    ['86+', 'Client'],
                    ['4.9', 'Clutch Rating'],
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
        if (in_array($prefix, ['performance-hero', 'performance-contact'], true)) {
            $fragment = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            try {
                $fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.view('partials.performance-enquiry', compact('prefix'))->render().'</body></html>');
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }

            return $document->importNode($fragment->getElementsByTagName('form')->item(0), true);
        }

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
