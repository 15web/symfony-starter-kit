<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

/**
 * Список ошибок
 */
final readonly class ApiErrorResponse
{
    private bool $isError;

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(private string $message, private array $errors, private int $code)
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

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
