<?php

namespace App\Services\Content;

use App\Models\Post;

class PostSeoAnalysis
{
    /** @return array{seo_score: int, readability_score: int, analysis: array<string, array{status: string, weight: int}>} */
    public function analyze(Post $post): array
    {
        $text = mb_strtolower($post->body);
        $keyword = mb_strtolower(trim((string) $post->focus_keyword));
        $words = preg_split('/[\s\p{P}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordCount = count($words);
        $firstWords = implode(' ', array_slice($words, 0, max(1, (int) ceil($wordCount / 10))));
        $occurrences = $keyword === '' ? 0 : substr_count($text, $keyword);
        $density = $wordCount === 0 ? 0 : ($occurrences * max(1, count(preg_split('/\s+/u', $keyword) ?: [])) / $wordCount) * 100;
        $has = fn (string $value): bool => $keyword !== '' && str_contains(mb_strtolower($value), $keyword);
        $checks = [
            'focus_keyword' => ['status' => $keyword === '' ? 'fail' : 'pass', 'weight' => 5],
            'title' => ['status' => $has($post->title) ? 'pass' : 'fail', 'weight' => 10],
            'seo_title' => ['status' => $has((string) $post->seo_title) ? 'pass' : 'fail', 'weight' => 10],
            'seo_description' => ['status' => $has((string) $post->seo_description) ? 'pass' : 'fail', 'weight' => 8],
            'seo_title_length' => ['status' => $this->lengthStatus($post->seo_title, 30, 60), 'weight' => 5],
            'seo_description_length' => ['status' => $this->lengthStatus($post->seo_description, 120, 160), 'weight' => 5],
            'url' => ['status' => $has($post->slug) ? 'pass' : 'fail', 'weight' => 7],
            'first_paragraph' => ['status' => $has($firstWords) ? 'pass' : 'fail', 'weight' => 8],
            'density' => ['status' => $density >= 0.5 && $density <= 2.5 ? 'pass' : ($density > 0 ? 'improve' : 'fail'), 'weight' => 8],
            'content_length' => ['status' => $wordCount >= 800 ? 'pass' : ($wordCount >= 300 ? 'improve' : 'fail'), 'weight' => 8],
            'secondary_keywords' => ['status' => $this->secondaryStatus($post->secondary_keywords, $text), 'weight' => 2],
        ];
        $score = 0.0;
        foreach ($checks as $check) {
            $score += $check['weight'] * match ($check['status']) {
                'pass' => 1, 'improve' => .5, default => 0
            };
        }
        $totalWeight = array_sum(array_column($checks, 'weight'));
        $sentences = preg_split('/[.!?]+/u', $post->body, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $average = $sentences === [] ? 0 : $wordCount / count($sentences);
        $readability = min(100, max(0, (int) round(100 - max(0, $average - 12) * 5)));

        return [
            'seo_score' => (int) round(($score / $totalWeight) * 100),
            'readability_score' => $readability,
            'analysis' => $checks,
        ];
    }

    /** @param array<int, string>|string|null $keywords */
    private function secondaryStatus(array|string|null $keywords, string $text): string
    {
        if (! is_array($keywords)) {
            return 'improve';
        }
        if ($keywords === []) {
            return 'improve';
        }
        foreach ($keywords as $keyword) {
            if (! str_contains($text, mb_strtolower($keyword))) {
                return 'fail';
            }
        }

        return 'pass';
    }

    private function lengthStatus(?string $value, int $minimum, int $maximum): string
    {
        $length = mb_strlen(trim((string) $value));
        if ($length >= $minimum && $length <= $maximum) {
            return 'pass';
        }

        return $length > 0 ? 'improve' : 'fail';
    }
}
