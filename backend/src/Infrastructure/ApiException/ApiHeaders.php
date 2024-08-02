<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

/**
 * Интерфейс для добавления заголовков API исключений
 */
interface ApiHeaders
{
    /**
     * @return array<non-empty-string, string>
     */
    public function getHeaders(): array;
}
