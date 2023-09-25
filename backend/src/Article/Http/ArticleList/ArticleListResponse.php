<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Infrastructure\Pagination\PaginationResponse;

/**
 * Ответ списка статей с пагинацией
 */
final readonly class ArticleListResponse
{
    /**
     * @param ArticleListData[] $data
     */
    public function __construct(
        public array $data,
        public PaginationResponse $pagination
    ) {}
}
