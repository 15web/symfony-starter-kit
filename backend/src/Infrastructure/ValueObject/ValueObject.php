<?php

declare(strict_types=1);

namespace App\Infrastructure\ValueObject;

/**
 * Интерфейс для всех ValueObject
 */
interface ValueObject
{
    public function equalTo(self $other): bool;
}
