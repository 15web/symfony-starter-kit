<?php

declare(strict_types=1);

namespace App\Article\Http;

/**
 * Класс ответа ручки InfoAction
 */
final readonly class InfoData
{
    public function __construct(
        public string $title,
        public string $body,
    ) {
    }
}
