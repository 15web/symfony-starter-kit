<?php

declare(strict_types=1);

namespace App\Article\Http;

/**
 * Информация о статье
 */
final readonly class ArticleInfoData
{
    public function __construct(
        public string $title,
        public string $body,
    ) {}
}
