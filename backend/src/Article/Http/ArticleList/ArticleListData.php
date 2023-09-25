<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

/**
 * Данные для списка статей
 */
final readonly class ArticleListData
{
    public function __construct(
        public string $title,
        public string $alias,
        public string $body,
    ) {}
}
