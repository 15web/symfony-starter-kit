<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Infrastructure\Pagination\PaginationResponse;

/**
 * Ответ списка статей с пагинацией
 */
final class ArticleListResponse
{
    /**
     * @param ArticleListData[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationResponse $pagination
    ) {
    }
}
