<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

/**
 * Объект запроса для обновления статьи
 */
final readonly class UpdateRequest
{
    /**
     * @param non-empty-string $title
     * @param non-empty-string $alias
     */
    public function __construct(
        public string $title,
        public string $alias,
        public string $body = '',
    ) {}
}
