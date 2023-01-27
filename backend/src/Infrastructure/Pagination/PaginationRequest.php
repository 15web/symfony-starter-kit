<?php

declare(strict_types=1);

namespace App\Infrastructure\Pagination;

use Webmozart\Assert\Assert;

final class PaginationRequest
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 10,
    ) {
        Assert::positiveInteger($page);
        Assert::positiveInteger($perPage);
    }

    public function getOffset(): int
    {
        $offset = $this->page - 1;

        return $offset * $this->perPage;
    }
}
