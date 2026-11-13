<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class ContentHtml
{
    /** @var list<string> */
    private const ALLOWED_ELEMENTS = [
        'a', 'article', 'b', 'blockquote', 'br', 'caption', 'code', 'dd', 'del', 'div', 'dl', 'dt',
        'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li',
        'ol', 'p', 'pre', 'section', 'small', 'span', 'strong', 'sub', 'summary', 'sup', 'table',
        'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    /** @var list<string> */
    private const DROP_ELEMENTS = ['iframe', 'object', 'script', 'style', 'svg', 'template', 'video', 'audio', 'form'];

    /** @var list<string> */
    private const ALLOWED_ATTRIBUTES = [
        'alt', 'aria-label', 'colspan', 'decoding', 'height', 'href', 'loading', 'rel', 'rowspan',
        'src', 'target', 'title', 'width',
    ];

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html) ?? '';

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body><div id="trivo-content-root">'.$html.'</div></body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $nodes = (new DOMXPath($document))->query('//*[@id="trivo-content-root"]');
        $root = $nodes === false ? null : $nodes->item(0);
        if (! $root instanceof DOMElement) {
            throw new \RuntimeException('Imported content could not be parsed for sanitization.');
        }

        $headingOneCount = 0;
        $this->cleanChildren($root, $document, $headingOneCount);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    public function hasHeadingOne(string $html): bool
    {
        return preg_match('/<h1(?:\s|>)/i', $html) === 1;
    }

    private function cleanChildren(DOMNode $parent, DOMDocument $document, int &$headingOneCount): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $parent->removeChild($child);
                }

                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_ELEMENTS, true)) {
                $parent->removeChild($child);

                continue;
            }

            if ($tag === 'h1' && ++$headingOneCount > 1) {
                $replacement = $document->createElement('h2');
                foreach (iterator_to_array($child->attributes) as $attribute) {
                    $replacement->setAttribute($attribute->name, $attribute->value);
                }
                while ($child->firstChild) {
                    $replacement->appendChild($child->firstChild);
                }
                $parent->replaceChild($replacement, $child);
                $child = $replacement;
                $tag = 'h2';
            }

            $this->cleanChildren($child, $document, $headingOneCount);
            if (! in_array($tag, self::ALLOWED_ELEMENTS, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                if (! in_array($name, self::ALLOWED_ATTRIBUTES, true)
                    || (in_array($name, ['width', 'height', 'colspan', 'rowspan'], true) && ! ctype_digit($attribute->value))) {
                    $child->removeAttributeNode($attribute);

                    continue;
                }

                if (in_array($name, ['href', 'src'], true)) {
                    $url = $this->localizeUrl($attribute->value);
                    if (! $this->safeUrl($url, $name === 'src')) {
                        $child->removeAttributeNode($attribute);

                        continue;
                    }
                    $child->setAttribute($name, $url);
                }
            }

            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private function safeUrl(string $url, bool $image): bool
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $url) || str_starts_with($url, '//')) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme === '') {
            return true;
        }

        return $image
            ? in_array($scheme, ['http', 'https'], true)
            : in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private function localizeUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['host'] ?? '')), ['trivo.in', 'www.trivo.in'], true)
            || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return $url;
        }

        return ($parts['path'] ?? '/')
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
