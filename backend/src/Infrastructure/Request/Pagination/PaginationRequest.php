<?php

declare(strict_types=1);

namespace App\Infrastructure\Request\Pagination;

/**
 * Запрос на пагинацию
 */
final readonly class PaginationRequest
{
    /**
     * @param int<0, max> $offset
     * @param positive-int $limit
     */
    public function __construct(public int $offset = 0, public int $limit = 10) {}
}
