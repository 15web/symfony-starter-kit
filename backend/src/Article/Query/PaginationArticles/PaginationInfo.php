<?php

declare(strict_types=1);

namespace App\Article\Query\PaginationArticles;

final class PaginationInfo
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $itemsPerPage,
        public readonly int $totalCount,
        public readonly int $currentPage,
        public readonly int $pageCount,
    ) {
    }
}
