<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

/**
 * Общий интерфейс АПИ исключений
 */
interface ApiException
{
    public function getHttpCode(): int;

    public function getApiCode(): int;

    public function getErrorMessage(): string;

    public function getErrors(): array;
}
