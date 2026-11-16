<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class BlogPostLayout
{
    public function heading(string $html, string $fallback): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body>'.$html.'</body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $body = $document->getElementsByTagName('body')->item(0);
        foreach ($body?->childNodes ?? [] as $child) {
            if (! $child instanceof DOMElement) {
                if (trim($child->textContent) === '') {
                    continue;
                }

                break;
            }

            if (in_array(strtolower($child->tagName), ['h1', 'h2'], true) && trim($child->textContent) !== '') {
                return trim($child->textContent);
            }

            break;
        }

        return $fallback;
    }

    /**
     * @return array{html: string, featuredImage: array{src: string, alt: string}|null}
     */
    public function prepare(string $html, string $headingText, bool $extractFeaturedImage = false): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body><div id="trivo-blog-root">'.$html.'</div></body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $nodes = (new DOMXPath($document))->query('//*[@id="trivo-blog-root"]');
        $root = $nodes === false ? null : $nodes->item(0);
        if (! $root instanceof DOMElement) {
            throw new \RuntimeException('Blog content could not be prepared for rendering.');
        }

        $featuredImage = null;
        if ($extractFeaturedImage) {
            $images = (new DOMXPath($document))->query('.//img[@src]', $root);
            $image = $images === false ? null : $images->item(0);
            if ($image instanceof DOMElement) {
                $src = trim($image->getAttribute('src'));
                if ($src !== '') {
                    $featuredImage = ['src' => $src, 'alt' => $image->getAttribute('alt')];
                    $parent = $image->parentNode;
                    $parent?->removeChild($image);
                    $this->removeEmptyImageAncestors($parent, $root);
                }
            }
        }

        $firstElement = null;
        foreach ($root->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $firstElement = $child;
                break;
            }
        }
        if ($firstElement instanceof DOMElement
            && in_array(strtolower($firstElement->tagName), ['h1', 'h2'], true)
            && $this->normalizedText($firstElement->textContent) === $this->normalizedText($headingText)) {
            $firstElement->parentNode?->removeChild($firstElement);
        }

        foreach (iterator_to_array((new DOMXPath($document))->query('.//h1', $root) ?: []) as $heading) {
            if (! $heading instanceof DOMElement) {
                continue;
            }

            $replacement = $document->createElement('h2');
            while ($heading->firstChild) {
                $replacement->appendChild($heading->firstChild);
            }
            $heading->parentNode?->replaceChild($replacement, $heading);
        }

        foreach (iterator_to_array((new DOMXPath($document))->query('.//summary[not(parent::details)]', $root) ?: []) as $summary) {
            if (! $summary instanceof DOMElement || ! $summary->parentNode) {
                continue;
            }

            $parent = $summary->parentNode;
            $details = $document->createElement('details');
            $details->setAttribute('class', 'blog-post__faq-item');
            $parent->insertBefore($details, $summary);

            for ($node = $summary; $node instanceof DOMNode;) {
                if ($node !== $summary && $node instanceof DOMElement
                    && (strtolower($node->tagName) === 'summary' || preg_match('/^h[1-6]$/i', $node->tagName))) {
                    break;
                }

                $next = $node->nextSibling;
                $details->appendChild($node);
                $node = $next;
            }
        }

        foreach ((new DOMXPath($document))->query('.//h1 | .//h2 | .//h3', $root) ?: [] as $heading) {
            if ($heading instanceof DOMElement && preg_match('/^(faq(?:s|\'s)?|frequently asked questions)$/i', trim($heading->textContent))) {
                $heading->setAttribute('class', trim($heading->getAttribute('class').' blog-post__faq-heading'));
            }
        }

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return ['html' => trim($clean), 'featuredImage' => $featuredImage];
    }

    /**
     * @return array{src: string, alt: string}|null
     */
    public function firstImage(string $html): ?array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body>'.$html.'</body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $image = $document->getElementsByTagName('img')->item(0);
        if (! $image instanceof DOMElement || trim($image->getAttribute('src')) === '') {
            return null;
        }

        return ['src' => $image->getAttribute('src'), 'alt' => $image->getAttribute('alt')];
    }

    private function removeEmptyImageAncestors(?DOMNode $node, DOMElement $root): void
    {
        while ($node instanceof DOMElement && $node !== $root && trim($node->textContent) === ''
            && $node->getElementsByTagName('img')->length === 0) {
            $parent = $node->parentNode;
            $parent?->removeChild($node);
            $node = $parent;
        }
    }

    private function normalizedText(string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text));
    }
}
