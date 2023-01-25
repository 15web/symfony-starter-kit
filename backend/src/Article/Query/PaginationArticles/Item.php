<?php

declare(strict_types=1);

namespace App\Article\Query\PaginationArticles;

final class Item
{
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
    ) {
    }
}
