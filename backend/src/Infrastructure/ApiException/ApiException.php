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

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getErrors(): array;
}
