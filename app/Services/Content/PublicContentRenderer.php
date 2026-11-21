<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;
use App\Support\ContentHtml;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicContentRenderer
{
    public function render(Page|Post $content): View
    {
        if ($content instanceof Page && $content->path === '/marketing-agency-in-bahrain') {
            return view('pages.marketing-agency-in-bahrain', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/marketing-solutions') {
            return view('pages.marketing-solutions', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/about-us') {
            return view('pages.about', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/portfolio') {
            return view('pages.portfolio', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/contact') {
            return view('pages.contact', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/meet-the-team') {
            return view('pages.meet-the-team', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/awards') {
            return view('pages.awards', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/career') {
            return view('pages.career', ['record' => $content]);
        }

        if ($content instanceof Page && $content->path === '/return-refund-policy') {
            return view('pages.return-refund-policy', ['record' => $content]);
        }

        $body = $content instanceof Page ? $this->pageBody($content) : $content->body;
        $bodyHtml = app(ContentHtml::class)->clean($body);
        $hasHeadingOne = app(ContentHtml::class)->hasHeadingOne($bodyHtml);

        if ($content instanceof Page && $content->path === '/seo-company-mumbai') {
            $layout = app(ServicePageLayout::class)->render($bodyHtml, $content->path);
            $document = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            try {
                $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$layout.'</body></html>');
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            $xpath = new DOMXPath($document);
            $cards = function (string $section, string $selector) use ($xpath): array {
                $items = [];
                foreach ($xpath->query('//*[contains(@class,"'.$section.'")]'.$selector) as $node) {
                    $heading = $xpath->query('./h5|./summary', $node)->item(0);
                    $paragraphs = [];
                    foreach ($xpath->query('./p', $node) as $paragraph) {
                        $paragraphs[] = trim($paragraph->textContent);
                    }
                    $items[] = ['title' => trim($heading?->textContent ?? ''), 'copy' => implode(' ', $paragraphs)];
                }
                return $items;
            };

            return view('pages.seo-company-mumbai', [
                'record' => $content,
                'benefits' => $cards('service-section--mumbai-benefit-item', '//article[contains(@class,"service-feature-card")]'),
                'services' => $cards('service-section--mumbai-services', '//article[contains(@class,"service-feature-card")]'),
                'reasons' => $cards('service-section--mumbai-why', '//details'),
                'faqs' => $cards('service-section--mumbai-faq', '//details'),
                'awardImages' => array_map(fn ($image) => $image->getAttribute('src'), iterator_to_array($xpath->query('//*[contains(@class,"mumbai-award-logos")]/img'))),
            ]);
        }

        if ($content instanceof Post) {
            $blogPostLayout = app(BlogPostLayout::class);
            $articleHeading = ($content->content_kind ?? 'article') === 'article' ? $content->title : $blogPostLayout->heading($bodyHtml, $content->title);
            $featuredMedia = $content->featured_media_id
                ? Media::query()->find($content->featured_media_id)
                : null;
            if ($featuredMedia && $featuredMedia->processing_status !== 'ready') {
                $featuredMedia = null;
            }
            $preparedBody = $blogPostLayout->prepare(
                $bodyHtml,
                $articleHeading,
                ($content->body_format ?? 'legacy') === 'legacy' && (! $featuredMedia || $featuredMedia->processing_status !== 'ready')
            );

            $withContents = app(BlogBody::class)->tableOfContents($preparedBody['html'], (bool) $content->show_toc);

            return view('pages.blog-post', [
                'article' => [
                    'title' => $content->title,
                    'heading' => $articleHeading,
                    'date' => $content->published_at?->format('F j, Y') ?? '',
                    'content' => $bodyHtml,
                    'seo_title' => $content->seo_title,
                    'seo_description' => $content->seo_description ?: ($content->excerpt ?: Str::limit(strip_tags($bodyHtml), 180)),
                    'canonical_url' => $content->canonical_url,
                    'robots' => $content->robots,
                    'author' => $content->author?->name, 'category' => $content->category?->name,
                    'tags' => $content->tags()->pluck('name')->all(), 'reading_time' => $content->reading_time,
                ],
                'bodyHtml' => $withContents['html'], 'toc' => $withContents['toc'],
                'featuredMedia' => $featuredMedia,
                'featuredImage' => $preparedBody['featuredImage'],
            ]);
        }

        if ($content instanceof Page && $content->path === '/') {
            return view('pages.imported-home', [
                'record' => $content,
                'bodyHtml' => $this->homeLayout($bodyHtml),
                'hasHeadingOne' => $hasHeadingOne,
            ]);
        }

        if ($content instanceof Page && ServicePageLayout::supports($content->path)) {
            $testimonials = $content->path === '/graphic-design' ? $this->publishedTestimonials() : [];

            return view('pages.imported-service', [
                'record' => $content,
                'bodyHtml' => app(ServicePageLayout::class)->render($bodyHtml, $content->path, $testimonials),
            ]);
        }

        return view('pages.imported-content', [
            'record' => $content,
            'bodyHtml' => $bodyHtml,
            'hasHeadingOne' => $hasHeadingOne,
        ]);
    }

    private function homeLayout(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body><div id="trivo-home-root">'.$html.'</div></body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $nodes = (new DOMXPath($document))->query('//*[@id="trivo-home-root"]');
        $root = $nodes === false ? null : $nodes->item(0);
        if (! $root instanceof DOMElement) {
            throw new \RuntimeException('Imported homepage content could not be prepared for rendering.');
        }

        $elements = array_values(array_filter(
            iterator_to_array($root->childNodes),
            static fn (DOMNode $node): bool => $node instanceof DOMElement
        ));
        $expectedTags = ['h1', 'p', 'img', 'img', 'img', 'p', 'img', 'img', 'img', 'img', 'img'];
        $actualTags = array_map(static fn (DOMElement $element): string => strtolower($element->tagName), array_slice($elements, 0, count($expectedTags)));
        if ($actualTags !== $expectedTags) {
            return $html;
        }

        $hero = $document->createElement('section');
        $hero->setAttribute('class', 'home-hero');
        $heroInner = $document->createElement('div');
        $heroInner->setAttribute('class', 'home-hero__inner');
        $heroCopy = $document->createElement('div');
        $heroCopy->setAttribute('class', 'home-hero__copy');
        $heroVisual = $document->createElement('div');
        $heroVisual->setAttribute('class', 'home-hero__visual');

        $heading = $elements[0];
        $headingText = trim($heading->textContent);
        if (preg_match('/^(Digital marketing)(\s+that.+)$/iu', $headingText, $matches) === 1) {
            while ($heading->firstChild) {
                $heading->removeChild($heading->firstChild);
            }
            $darkText = $document->createElement('span');
            $darkText->setAttribute('class', 'home-hero__dark-title');
            $darkText->appendChild($document->createTextNode($matches[1]));
            $lightText = $document->createElement('span');
            $lightText->setAttribute('class', 'home-hero__light-title');
            $lightText->appendChild($document->createTextNode($matches[2]));
            $heading->appendChild($darkText);
            $heading->appendChild($lightText);
        }

        $heroCopy->appendChild($heading);
        $heroCopy->appendChild($elements[1]);
        $auditAction = $document->createElement('div');
        $auditAction->setAttribute('class', 'home-hero__audit');
        $emailPlaceholder = $document->createElement('input');
        $emailPlaceholder->setAttribute('class', 'home-hero__email-placeholder');
        $emailPlaceholder->setAttribute('type', 'email');
        $emailPlaceholder->setAttribute('placeholder', 'Email');
        $emailPlaceholder->setAttribute('aria-label', 'Your email address');
        $auditAction->appendChild($emailPlaceholder);
        $auditLink = $document->createElement('a', 'NEXT');
        $auditLink->setAttribute('class', 'home-hero__cta');
        $auditLink->setAttribute('href', '#home-audit');
        $auditLink->setAttribute('aria-label', 'Get your free marketing audit');
        $auditAction->appendChild($auditLink);
        $heroCopy->appendChild($auditAction);

        foreach ([2 => 'chart', 3 => 'campaign', 4 => 'traffic'] as $index => $class) {
            $image = $elements[$index];
            $image->setAttribute('class', 'home-hero__image home-hero__image--'.$class);
            $heroVisual->appendChild($image);
        }

        $heroInner->appendChild($heroCopy);
        $heroInner->appendChild($heroVisual);
        $hero->appendChild($heroInner);

        $partners = $document->createElement('section');
        $partners->setAttribute('class', 'home-partners');
        $partners->appendChild($elements[5]);
        for ($index = 6; $index < 11; $index++) {
            $partners->appendChild($elements[$index]);
        }

        $sections = $document->createElement('div');
        $sections->setAttribute('class', 'home-sections');
        $currentSection = null;
        $currentGroup = null;
        $resultsSection = null;
        $serviceTabs = null;
        $serviceTabList = null;
        $servicePanelIndex = 0;
        $insertCaseStudiesAfterViewAll = false;
        $caseStudies = $this->homeCaseStudies($document);
        $resultsTestimonials = $this->homeResultsTestimonials($document);
        $serviceLabels = [
            'Search engine optimization',
            'Paid search marketing',
            'Conversion rate optimization',
            'Social media marketing',
            'Performance marketing',
        ];

        foreach (array_slice($elements, 11) as $element) {
            $tag = strtolower($element->tagName);
            if ($tag === 'br') {
                continue;
            }

            $text = trim($element->textContent);
            $startsClientSection = $tag === 'h3' && str_contains(strtolower($text), 'clients we work with');
            if ($tag === 'h2' || $startsClientSection) {
                $currentSection = $document->createElement('section');
                $sectionClass = match (true) {
                    $startsClientSection => 'home-section home-section--clients',
                    str_contains(strtolower($text), 'what we do') => 'home-section home-section--services',
                    str_contains(strtolower($text), 'about trivo') => 'home-section home-section--about',
                    str_contains(strtolower($text), 'driving digital success') => 'home-section home-section--results',
                    str_contains(strtolower($text), 'team behind') => 'home-section home-section--team',
                    str_contains(strtolower($text), 'blog') => 'home-section home-section--blog',
                    default => 'home-section',
                };
                $currentSection->setAttribute('class', $sectionClass);
                $sections->appendChild($currentSection);
                if (str_contains($sectionClass, '--results')) {
                    $resultsSection = $currentSection;
                }
                $currentGroup = null;
                $serviceTabs = null;
                $serviceTabList = null;
                $servicePanelIndex = 0;
                if ($startsClientSection) {
                    $currentGroup = $document->createElement('div');
                    $currentGroup->setAttribute('class', 'home-section__group');
                    $currentSection->appendChild($currentGroup);
                    $currentGroup->appendChild($element);

                    continue;
                }
            }

            if (! $currentSection instanceof DOMElement) {
                $currentSection = $document->createElement('section');
                $currentSection->setAttribute('class', 'home-section');
                $sections->appendChild($currentSection);
            }

            if ($tag === 'h3' || $tag === 'h6') {
                $currentGroup = $document->createElement('div');
                $groupClass = 'home-section__group';
                if ($tag === 'h3' && str_contains(strtolower($text), 'customer data')) {
                    $groupClass .= ' home-section__group--customer-data';
                } elseif ($tag === 'h6' && str_contains(strtolower($text), 'customers are some')) {
                    $groupClass .= ' home-section__group--customer-overline';
                } elseif ($tag === 'h3' && str_contains(strtolower($text), 'fastest growing companies') && $caseStudies instanceof DOMElement) {
                    $groupClass .= ' home-section__group--fastest';
                } elseif ($tag === 'h6' && str_contains(strtolower($text), 'deliver reliable technology')) {
                    $groupClass .= ' home-section__group--results-intro';
                } elseif ($tag === 'h3' && str_contains(strtolower($text), 'driving digital revenue')) {
                    $groupClass .= ' home-section__group--results-testimonials';
                } elseif ($tag === 'h3' && str_contains(strtolower($text), 'powering your growth with the expertise')) {
                    $groupClass .= ' home-section__group--team';
                }
                $currentGroup->setAttribute('class', $groupClass);
                $currentSection->appendChild($currentGroup);
                $currentGroup->appendChild($element);
                $insertCaseStudiesAfterViewAll = $tag === 'h3' && str_contains(strtolower($text), 'fastest growing companies');
                if ($tag === 'h6' && str_contains(strtolower($text), 'deliver reliable technology')) {
                    $currentGroup->appendChild($this->homeResultsStats($document));
                } elseif ($tag === 'h3' && str_contains(strtolower($text), 'driving digital revenue') && $resultsTestimonials instanceof DOMElement) {
                    $currentGroup->appendChild($resultsTestimonials);
                }

                continue;
            }

            if ($tag === 'section' && str_contains($currentSection->getAttribute('class'), '--services')) {
                if (! $serviceTabs instanceof DOMElement) {
                    $serviceTabs = $document->createElement('div');
                    $serviceTabs->setAttribute('class', 'home-service-tabs');

                    $serviceTabList = $document->createElement('div');
                    $serviceTabList->setAttribute('class', 'home-service-tabs__list');
                    $serviceTabList->setAttribute('role', 'tablist');
                    $serviceTabList->setAttribute('aria-label', 'Marketing services');
                    $serviceTabs->appendChild($serviceTabList);
                    $currentSection->appendChild($serviceTabs);
                }

                $label = $serviceLabels[$servicePanelIndex] ?? 'Marketing service';
                $tabId = 'home-service-tab-'.$servicePanelIndex;
                $panelId = 'home-service-panel-'.$servicePanelIndex;
                $tab = $document->createElement('button', $label);
                $tab->setAttribute('class', 'home-service-tabs__tab');
                $tab->setAttribute('type', 'button');
                $tab->setAttribute('role', 'tab');
                $tab->setAttribute('id', $tabId);
                $tab->setAttribute('aria-controls', $panelId);
                $tab->setAttribute('aria-selected', $servicePanelIndex === 0 ? 'true' : 'false');
                $tab->setAttribute('tabindex', $servicePanelIndex === 0 ? '0' : '-1');
                $tab->setAttribute('data-panel-id', $panelId);
                $serviceTabList?->appendChild($tab);

                $element->setAttribute('class', 'home-service-tabs__panel');
                $element->setAttribute('id', $panelId);
                $element->setAttribute('role', 'tabpanel');
                $element->setAttribute('aria-labelledby', $tabId);
                $copy = $document->createElement('div');
                $copy->setAttribute('class', 'home-service-tabs__copy');
                $statistics = $document->createElement('div');
                $statistics->setAttribute('class', 'home-service-tabs__statistics');
                while ($element->firstChild) {
                    $child = $element->firstChild;
                    if ($child instanceof DOMElement && strtolower($child->tagName) === 'section') {
                        $statistics->appendChild($child);
                    } else {
                        $copy->appendChild($child);
                    }
                }
                $element->appendChild($copy);
                if ($statistics->hasChildNodes()) {
                    $element->appendChild($statistics);
                }
                if ($servicePanelIndex > 0) {
                    $element->setAttribute('hidden', '');
                }
                $serviceTabs->appendChild($element);
                $servicePanelIndex++;

                continue;
            }

            if ($tag === 'img'
                && str_contains($currentSection->getAttribute('class'), '--team')
                && basename((string) parse_url($element->getAttribute('src'), PHP_URL_PATH)) === 'Untitled-design.png'
                && $resultsSection instanceof DOMElement) {
                $element->setAttribute('class', 'home-results__visual');
                $resultsSection->appendChild($element);

                continue;
            }

            ($currentGroup ?? $currentSection)->appendChild($element);
            if ($insertCaseStudiesAfterViewAll && $tag === 'a' && str_contains(strtolower($text), 'view all')) {
                if ($caseStudies instanceof DOMElement) {
                    ($currentGroup ?? $currentSection)->appendChild($caseStudies);
                }
                $insertCaseStudiesAfterViewAll = false;
            }
        }

        $aboutSections = (new DOMXPath($document))->query('.//section[contains(@class, "home-section--about")]', $sections);
        foreach ($aboutSections ?: [] as $about) {
            $group = (new DOMXPath($document))->query('./div[contains(@class, "home-section__group--customer-data")]', $about)?->item(0);
            if (! $group instanceof DOMElement) {
                continue;
            }
            $about->setAttribute('class', 'trivo-about');
            $label = $about->getElementsByTagName('h2')->item(0);
            $label?->setAttribute('class', 'trivo-about__label');
            if ($label) {
                $label->textContent = 'About Trivo';
            }
            $group->setAttribute('class', 'trivo-about__intro');
            $copy = $document->createElement('div');
            $copy->setAttribute('class', 'trivo-about__copy');
            $milestones = $document->createElement('ul');
            $milestones->setAttribute('class', 'trivo-about__milestones');
            $hasLink = false;
            foreach (iterator_to_array($group->childNodes) as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child->tagName === 'h3') {
                    $child->setAttribute('class', 'trivo-about__title');
                    $child->textContent = 'Customer Data,';
                    $child->appendChild($document->createElement('br'));
                    $child->appendChild($document->createTextNode('Thoughtfully Built'));
                } elseif ($child->tagName === 'a' || ! $hasLink) {
                    if ($child->tagName === 'a' && ! $child->hasAttribute('href')) {
                        $child->setAttribute('href', route('about'));
                    }
                    $hasLink = $hasLink || $child->tagName === 'a';
                    $copy->appendChild($child);
                } else {
                    $item = $document->createElement('li');
                    $item->appendChild($child);
                    $milestones->appendChild($item);
                }
            }
            $group->appendChild($copy);
            $about->insertBefore($milestones, $group->nextSibling);
            $caseSection = $document->createElement('section');
            $caseSection->setAttribute('class', 'home-showcase');
            $sections->insertBefore($caseSection, $about->nextSibling);
            while ($milestones->nextSibling) {
                $extra = $milestones->nextSibling;
                if ($extra instanceof DOMElement && str_contains(strtolower($extra->textContent), 'get your free marketing audit')) {
                    $about->removeChild($extra);
                } else {
                    $caseSection->appendChild($extra);
                }
            }
            $this->appendHomeFragment($document, $caseSection, view('partials.home-enquiry', ['variant' => 'audit'])->render());
        }

        $xpath = new DOMXPath($document);
        foreach ($xpath->query('.//section[contains(@class, "home-section--blog")]', $sections) ?: [] as $blog) {
            $posts = Post::query()->articles()->where('status', 'published')->where('published_at', '<=', now())
                ->orderByDesc('published_at')->limit(2)->get();
            $media = Media::query()->whereIn('id', $posts->pluck('featured_media_id'))->get()->keyBy('id');
            $this->appendHomeFragment($document, $blog, view('partials.home-blog-cards', compact('posts', 'media'))->render());
        }
        foreach ($xpath->query('.//a[not(@href)]', $sections) ?: [] as $link) {
            $label = strtolower(trim($link->textContent));
            $link->setAttribute('href', match (true) {
                str_contains($label, 'solutions') => url('/marketing-solutions'),
                str_contains($label, 'team') => url('/meet-the-team'),
                str_contains($label, 'view more') => route('blog'),
                $label === 'view all' => route('portfolio'),
                default => route('contact'),
            });
        }

        while ($root->firstChild) {
            $root->removeChild($root->firstChild);
        }
        $root->appendChild($hero);
        $root->appendChild($partners);
        $root->appendChild($sections);

        $layout = '';
        foreach ($root->childNodes as $child) {
            $layout .= $document->saveHTML($child);
        }

        return $layout;
    }

    private function appendHomeFragment(DOMDocument $document, DOMElement $parent, string $html): void
    {
        $fragment = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        foreach (iterator_to_array($fragment->getElementsByTagName('body')->item(0)->childNodes) as $child) {
            $parent->appendChild($document->importNode($child, true));
        }
    }

    private function homeCaseStudies(DOMDocument $document): ?DOMElement
    {
        $thumbnails = [
            '/luhaif-perfumes' => '2026/05/Luhaif-Thumbnail.png',
            '/mumbai-darshan' => '2026/05/Mumbai-Thumbnail.png',
            '/sell-sold' => '2026/05/Sell-Sold-Thumbnail.png',
            '/professional-frameworks' => '2026/05/Professional-Frameworks-Thumbnail.png',
            '/arihant-panel-fittings' => '2026/05/Arihant-Thumbnail.png',
            '/al-bushra-t-and-t' => '2026/03/Al-Bushra-Thumbnail.png',
            '/stocksovet' => '2026/03/Stock-Sovet-Thumbnail.png',
            '/rashidmiftahi' => '2026/03/Rashid-Miftahi-Thumbnail.png',
            '/areebatourism' => '2026/03/Areeba-Thumbnail.png',
            '/sanjari-recycling' => '2026/03/Sanjari-Thumbnail.png',
            '/holidayaapkliye' => '2026/05/Holiday-Aapke-Liye-Thumbnail.png',
            '/villaplanet' => '2026/05/Villa-Planet-Thumbnail.png',
            '/bradymorris' => '2026/05/Brady-Morris-Thumbnail.png',
            '/miftahi-shop' => '2026/05/Miftahi-Shop-Thumbnail.png',
        ];

        $posts = Post::query()
            ->whereIn('path', array_keys($thumbnails))
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->get(['title', 'path', 'published_at'])
            ->keyBy('path');

        $missing = [];
        foreach ($thumbnails as $path => $thumbnail) {
            if (! $posts->has($path) || ! is_file(public_path('wp-content/uploads/'.$thumbnail))) {
                $missing[] = $path;
            }
        }

        if ($missing !== []) {
            Log::warning('Homepage case studies are missing published post content or imported thumbnails.', [
                'paths' => $missing,
            ]);
        }

        $carousel = $document->createElement('div');
        $carousel->setAttribute('class', 'home-case-study-carousel');
        $carousel->setAttribute('role', 'region');
        $carousel->setAttribute('aria-label', 'Featured client case studies');
        $track = $document->createElement('div');
        $track->setAttribute('class', 'home-case-studies');
        $track->setAttribute('id', 'home-case-studies-track');
        $track->setAttribute('role', 'list');

        foreach ($thumbnails as $path => $thumbnail) {
            $post = $posts->get($path);
            if (! $post || ! is_file(public_path('wp-content/uploads/'.$thumbnail))) {
                continue;
            }

            $card = $document->createElement('article');
            $card->setAttribute('class', 'home-case-study');
            $card->setAttribute('role', 'listitem');
            $link = $document->createElement('a');
            $link->setAttribute('class', 'home-case-study__link');
            $link->setAttribute('href', $post->path);
            $image = $document->createElement('img');
            $image->setAttribute('src', asset('wp-content/uploads/'.$thumbnail));
            $image->setAttribute('alt', $post->title);
            $image->setAttribute('loading', 'lazy');
            $image->setAttribute('decoding', 'async');
            $link->appendChild($image);
            $card->appendChild($link);

            $date = $document->createElement('p');
            $date->appendChild($document->createTextNode($post->published_at->format('F Y')));
            $date->setAttribute('class', 'home-case-study__date');
            $card->appendChild($date);
            $title = $document->createElement('h4');
            $titleLink = $document->createElement('a');
            $titleLink->setAttribute('href', $post->path);
            $titleLink->appendChild($document->createTextNode($post->title));
            $title->appendChild($titleLink);
            $card->appendChild($title);
            $track->appendChild($card);
        }

        if (! $track->hasChildNodes()) {
            return null;
        }

        $carousel->appendChild($track);
        $pagination = $document->createElement('div');
        $pagination->setAttribute('class', 'home-case-study-pagination');
        $pagination->setAttribute('role', 'group');
        $pagination->setAttribute('aria-label', 'Choose a case study slide');
        $initialSlideCount = max(1, $track->childNodes->length - 3 + 1);
        for ($index = 0; $index < $initialSlideCount; $index++) {
            $button = $document->createElement('button');
            $button->setAttribute('class', 'home-case-study-pagination__button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('aria-label', 'Go to case study slide '.($index + 1));
            $button->setAttribute('aria-controls', 'home-case-studies-track');
            $button->setAttribute('data-slide-index', (string) $index);
            if ($index === 0) {
                $button->setAttribute('aria-current', 'true');
            }
            $pagination->appendChild($button);
        }
        $carousel->appendChild($pagination);

        return $carousel;
    }

    private function homeResultsStats(DOMDocument $document): DOMElement
    {
        $statistics = $document->createElement('div');
        $statistics->setAttribute('class', 'home-results__stats');

        foreach ([
            ['1,000+', 'Projects Successfully Delivered'],
            ['300+', 'Happy Clients Worldwide'],
            ['15+', 'Years of Industry Experience'],
            ['100+', 'Technologies & Tools Expertise'],
        ] as [$value, $label]) {
            $item = $document->createElement('div');
            $item->setAttribute('class', 'home-results__stat');
            $number = $document->createElement('strong');
            $number->appendChild($document->createTextNode($value));
            $text = $document->createElement('span');
            $text->appendChild($document->createTextNode($label));
            $item->appendChild($number);
            $item->appendChild($text);
            $statistics->appendChild($item);
        }

        return $statistics;
    }

    private function homeResultsTestimonials(DOMDocument $document): ?DOMElement
    {
        $testimonials = $this->publishedTestimonials();
        if ($testimonials === []) {
            return null;
        }

        $proof = $document->createElement('div');
        $proof->setAttribute('class', 'home-results__proof');
        $metric = $document->createElement('div');
        $metric->setAttribute('class', 'home-results__metric');
        $metricValue = $document->createElement('strong');
        $metricValue->appendChild($document->createTextNode('21%'));
        $metricLabel = $document->createElement('span');
        $metricLabel->appendChild($document->createTextNode('Average Traffic Increase for Clients'));
        $metric->appendChild($metricValue);
        $metric->appendChild($metricLabel);
        $proof->appendChild($metric);

        $cards = $document->createElement('div');
        $cards->setAttribute('class', 'home-results__testimonials');
        foreach ($testimonials as $testimonial) {
            $card = $document->createElement('blockquote');
            $card->setAttribute('class', 'home-results__testimonial');
            $quoteText = $document->createElement('p');
            $quoteText->appendChild($document->createTextNode($testimonial['quote']));
            $card->appendChild($quoteText);
            $attribution = $document->createElement('footer');
            $author = $document->createElement('cite');
            $author->appendChild($document->createTextNode($testimonial['title']));
            $attribution->appendChild($author);
            if (filled($testimonial['role'])) {
                $role = $document->createElement('span');
                $role->appendChild($document->createTextNode($testimonial['role']));
                $attribution->appendChild($role);
            }
            $card->appendChild($attribution);
            $cards->appendChild($card);
        }
        $proof->appendChild($cards);

        return $proof;
    }

    /**
     * @return list<array{quote: string, title: string, role: string|null}>
     */
    private function publishedTestimonials(): array
    {
        $testimonials = Testimonial::query()
            ->where('status', 'published')
            ->whereNull('path')
            ->orderBy('sort')
            ->orderBy('id')
            ->limit(4)
            ->get(['title', 'blocks', 'details']);
        $published = [];

        foreach ($testimonials as $testimonial) {
            $quote = collect($testimonial->blocks ?? [])
                ->first(fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'paragraph' && is_string($block['text'] ?? null));
            if (! is_array($quote)) {
                continue;
            }

            $details = is_array($testimonial->details) ? $testimonial->details : [];
            $published[] = [
                'quote' => $quote['text'],
                'title' => $testimonial->title,
                'role' => filled($details['role'] ?? null) ? (string) $details['role'] : null,
            ];
        }

        return $published;
    }

    private function pageBody(Page $page): string
    {
        $html = [];
        foreach ($page->blocks ?? [] as $block) {
            if (($block['type'] ?? null) === 'legacy_html' && is_string($block['html'] ?? null)) {
                $html[] = $block['html'];
            } elseif (($block['type'] ?? null) === 'heading' && filled($block['text'] ?? null)) {
                $html[] = '<h2>'.e($block['text']).'</h2>';
            } elseif (($block['type'] ?? null) === 'paragraph' && filled($block['text'] ?? null)) {
                $html[] = '<p>'.e($block['text']).'</p>';
            } elseif (($block['type'] ?? null) === 'link' && filled($block['url'] ?? null) && filled($block['label'] ?? null)) {
                $html[] = '<p><a href="'.e($block['url']).'">'.e($block['label']).'</a></p>';
            } elseif (($block['type'] ?? null) === 'image' && filled($block['media_id'] ?? null)) {
                $media = Media::query()->whereKey($block['media_id'])->first();
                if ($media && $media->processing_status === 'ready') {
                    $html[] = '<img src="'.e(Storage::disk($media->disk)->url($media->path)).'" alt="'.e($media->alt ?? '').'">';
                }
            }
        }

        return implode("\n", $html);
    }
}
