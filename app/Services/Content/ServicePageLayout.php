<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ServicePageLayout
{
    public const PATHS = [
        '/paid-search-marketing', '/social-media-marketing', '/search-engine-optimization',
        '/email-marketing', '/google-shopping', '/amazon-shopping', '/graphic-design',
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

    public function render(string $html, string $path): string
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
                $heading = (new DOMXPath($document))->query('./*[self::h2 or self::h3 or self::h4]', $copy)->item(0);
                $label = strtolower(trim($heading?->textContent ?? ''));
                if (in_array($label, ['creative designs', 'high quality', 'fast delivery'], true)) {
                    $class .= ' service-section--benefit';
                }
                if (str_contains($label, 'proof is in the numbers')) {
                    $class .= ' service-section--stats';
                }
            }
            $group->setAttribute('class', $class);
            $group->appendChild($copy);
            if ($media->hasChildNodes()) {
                $group->appendChild($media);
            }
            $this->featureCards($document, $copy);
            if (in_array($path, self::LOCATION_PATHS, true)) {
                $this->locationDetails($document, $copy);
                if ($copy->getElementsByTagName('details')->length > 0) {
                    $group->setAttribute('class', $group->getAttribute('class').' service-section--accordion');
                }
                $figures = (new DOMXPath($document))->query('./figure', $copy);
                if ($figures->length > 1) {
                    $gallery = $document->createElement('div');
                    $gallery->setAttribute('class', 'location-logo-grid');
                    $copy->insertBefore($gallery, $figures->item(0));
                    foreach (iterator_to_array($figures) as $figure) {
                        $gallery->appendChild($figure);
                    }
                }
            }
        }
        if ($path === '/email-marketing') {
            $this->tabs($document, $groups, ['PPC audit services', 'Remarketing services', 'Geofencing ad services', 'Social media ad services']);
        } elseif ($path === '/graphic-design') {
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
