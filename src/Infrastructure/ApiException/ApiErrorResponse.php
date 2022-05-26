<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

final class ApiErrorResponse
{
    private readonly bool $isError;

    public function __construct(private readonly string $errorMessage, private readonly int $code)
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
