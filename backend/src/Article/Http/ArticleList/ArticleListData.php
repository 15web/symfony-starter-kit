<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

/**
 * Данные для списка статей
 */
final class ArticleListData
{
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
        public readonly string $body,
    ) {
    }
}
