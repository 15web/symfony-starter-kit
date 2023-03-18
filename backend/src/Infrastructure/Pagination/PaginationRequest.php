<?php

declare(strict_types=1);

namespace App\Infrastructure\Pagination;

use Webmozart\Assert\Assert;

/**
 * Запрос на пагинацию
 */
final readonly class PaginationRequest
{
    public function __construct(
        public int $offset = 0,
        public int $limit = 10,
    ) {
        Assert::natural($offset);
        Assert::positiveInteger($limit);
    }
}
