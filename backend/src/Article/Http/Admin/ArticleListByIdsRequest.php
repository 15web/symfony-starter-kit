<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос на получение статей по списку Id
 */
final class ArticleListByIdsRequest
{
    /**
     * @param non-empty-list<Uuid> $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
