<?php

declare(strict_types=1);

namespace App\Article\Http;

/**
 * Класс ответа ручки InfoAction
 */
final class InfoData
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
    ) {
    }
}
