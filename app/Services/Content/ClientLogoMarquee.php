<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ClientLogoMarquee
{
    public function transform(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $xpath = new DOMXPath($document);
        $sections = $xpath->query(
            '//section[contains(concat(" ", normalize-space(@class), " "), " paid-search-clients ") '
            .'or contains(concat(" ", normalize-space(@class), " "), " home-section--clients ") '
            .'or .//*[self::h2 or self::h3 or self::h4][contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "clients we work with")]]'
        );
        if (! $sections || $sections->length === 0) {
            return $html;
        }

        foreach (iterator_to_array($sections) as $sectionIndex => $section) {
            if (! $section instanceof DOMElement) {
                continue;
            }

            if ($xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " home-clients__row ")]', $section)?->length > 0) {
                continue;
            }

            $logos = [];
            foreach (iterator_to_array($section->getElementsByTagName('img')) as $image) {
                if ($image instanceof DOMElement && $image->getAttribute('src') !== '') {
                    $logos[] = $image;
                }
            }

            if ($logos === []) {
                continue;
            }

            $existingHeading = $xpath->query(
                './/*[self::h2 or self::h3 or self::h4][contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "clients we work with")]',
                $section
            )?->item(0);
            $existingHeadingId = $existingHeading instanceof DOMElement ? $existingHeading->getAttribute('id') : '';
            $section->setAttribute('class', 'home-section home-section--clients');
            $headingId = $existingHeadingId !== '' ? $existingHeadingId : 'client-logos-heading-'.($sectionIndex + 1);
            $section->setAttribute('aria-labelledby', $headingId);
            while ($section->firstChild) {
                $section->removeChild($section->firstChild);
            }

            $group = $document->createElement('div');
            $group->setAttribute('class', 'home-section__group');
            $heading = $document->createElement('h3', 'Clients we work with');
            $heading->setAttribute('id', $headingId);
            $group->appendChild($heading);

            foreach (array_chunk($logos, (int) ceil(count($logos) / 2)) as $rowIndex => $clientRow) {
                $row = $document->createElement('div');
                $row->setAttribute('class', 'home-clients__row home-clients__row--'.($rowIndex + 1));
                $row->setAttribute('aria-label', 'Client logos row '.($rowIndex + 1));

                for ($copyIndex = 0; $copyIndex < 3; $copyIndex++) {
                    $track = $document->createElement('div');
                    $track->setAttribute('class', 'home-clients__track');
                    if ($copyIndex > 0) {
                        $track->setAttribute('aria-hidden', 'true');
                        $track->setAttribute('inert', '');
                    }

                    foreach ($clientRow as $image) {
                        $figure = $document->createElement('figure');
                        $logo = $image->cloneNode(true);
                        if ($logo instanceof DOMElement) {
                            if ($copyIndex > 0) {
                                $logo->setAttribute('alt', '');
                            } elseif ($logo->getAttribute('alt') === '') {
                                $logo->setAttribute('alt', 'Client logo');
                            }
                            if (! $logo->hasAttribute('width')) {
                                $logo->setAttribute('width', '300');
                            }
                            if (! $logo->hasAttribute('height')) {
                                $logo->setAttribute('height', '135');
                            }
                            $logo->setAttribute('loading', 'lazy');
                            $figure->appendChild($logo);
                        }
                        $track->appendChild($figure);
                    }

                    $row->appendChild($track);
                }

                $group->appendChild($row);
            }

            $section->appendChild($group);
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $html;
        }

        $output = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }
}
