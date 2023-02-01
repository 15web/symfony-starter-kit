<?php

declare(strict_types=1);

namespace App\Article\Http;

/**
 * Класс ответа ручки ListAction
 */
final class ListData
{
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
        public readonly string $body,
    ) {
    }
}
