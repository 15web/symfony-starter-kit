<?php

declare(strict_types=1);

namespace App\Infrastructure\Response\Pagination;

use Webmozart\Assert\Assert;

/**
 * Ответ пагинации. Содержит общее кол-во записей
 */
final readonly class PaginationResponse
{
    public function __construct(public int $total)
    {
        Assert::natural($total);
    }
}
