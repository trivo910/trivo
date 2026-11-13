<?php

namespace App\Services\Content;

class PostReadingTime
{
    public function minutes(string $body): int
    {
        $words = preg_split('/[\s\p{P}]+/u', trim($body), -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words ?: []) / 200));
    }
}
