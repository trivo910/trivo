<?php

namespace App\Services\Content;

class BlogPostSeo extends ContentSeo
{
    /** @return array<string, string> */
    public function schemaOptions(string $context = 'post'): array
    {
        return ['Article' => 'Article', 'BlogPosting' => 'Blog posting', 'WebPage' => 'Web page'];
    }
}
