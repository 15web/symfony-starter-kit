<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

/**
 * Сообщение ошибки
 */
final readonly class ApiErrorResponse
{
    private bool $isError;

    public function __construct(private string $errorMessage, private int $code)
    {
        $this->isError = true;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
