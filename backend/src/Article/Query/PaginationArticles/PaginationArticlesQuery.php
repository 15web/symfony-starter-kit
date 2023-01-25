<?php

declare(strict_types=1);

namespace App\Article\Query\PaginationArticles;

use Webmozart\Assert\Assert;

final class PaginationArticlesQuery
{
    public readonly int $page;
    public readonly int $count;

    public function __construct(
        ?int $page,
        ?int $count,
    ) {
        $this->page = $page ?? 1;
        $this->count = $count ?? 10;

        Assert::positiveInteger($this->page);
        Assert::oneOf($this->count, [
            10,
            20,
            50,
        ]);
    }
}
