<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMXPath;

class PaidSearchPageLayout
{
    /** Apply presentation to sanitized, database-owned page content. */
    public function render(string $html): string
    {
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
        $output->setAttribute('class', 'paid-search-content');
        $rows = [];
        $section = null;
        $mode = 'rows';
        $cards = null;
        $card = null;
        foreach ($nodes as $node) {
            $tag = strtolower($node->tagName);
            $text = strtolower(trim($node->textContent));
            if ($tag === 'h1' || $tag === 'br') {
                continue;
            }
            if ($tag === 'h6' && $text === 'what you get') {
                $mode = 'hidden';
                $section = $document->createElement('div');
                $section->setAttribute('hidden', '');
                $output->appendChild($section);
            } elseif ($tag === 'h6' && str_contains($text, 'power of paid search')) {
                $mode = 'results';
                $section = $document->createElement('section');
                $section->setAttribute('class', 'paid-search-results');
                $output->appendChild($section);
                $cards = $document->createElement('div');
                $cards->setAttribute('class', 'paid-search-result-cards');
            } elseif ($tag === 'h6' && $text === 'faq') {
                $mode = 'hidden';
                $section = $document->createElement('div');
                $section->setAttribute('hidden', '');
                $output->appendChild($section);
            } elseif ($tag === 'h3' && str_contains($text, 'clients we work with')) {
                $mode = 'clients';
                $section = $document->createElement('section');
                $section->setAttribute('class', 'paid-search-clients');
                $output->appendChild($section);
            }
            if ($mode === 'rows') {
                if ($tag === 'h4') {
                    $section = $document->createElement('section');
                    $section->setAttribute('class', count($rows) === 0 ? 'paid-search-intro' : 'paid-search-row');
                    $rows[] = $section;
                    $output->appendChild($section);
                }
                ($section ?? $output)->appendChild($node);
            } elseif ($mode === 'results') {
                if ($tag === 'h5') {
                    $card = $document->createElement('article');
                    $card->setAttribute('class', 'paid-search-result-card');
                    $cards->appendChild($card);
                    $section->appendChild($cards);
                }
                ($card ?? $section)->appendChild($node);
            } else {
                $section->appendChild($node);
            }
        }
        // The import places the next row's left-hand image after the prior row's image.
        foreach ($rows as $index => $row) {
            $images = array_values(array_filter(iterator_to_array($row->childNodes), fn ($node) => $node instanceof DOMElement && $node->tagName === 'img'));
            if (count($images) > 1 && isset($rows[$index + 1])) {
                $next = $rows[$index + 1];
                $next->insertBefore(end($images), $next->firstChild);
                $next->setAttribute('class', 'paid-search-row paid-search-row--reverse');
            }
            $copy = $document->createElement('div');
            $copy->setAttribute('class', 'paid-search-copy');
            foreach (iterator_to_array($row->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->tagName !== 'img' && ! ($index === 0 && $child->tagName === 'ul')) {
                    $copy->appendChild($child);
                }
            }
            $row->insertBefore($copy, $row->firstChild);
        }
        foreach ((new DOMXPath($document))->query('.//a[not(@href)]', $output) ?: [] as $link) {
            $link->setAttribute('href', url('/free-audit'));
        }

        return $document->saveHTML($output);
    }
}
