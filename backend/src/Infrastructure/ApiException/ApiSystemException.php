<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Override;
use Throwable;

/**
 * Системное исключение.
 */
final class ApiSystemException extends Exception implements ApiException
{
    private const string MESSAGE = 'Системная ошибка';

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(
        private readonly array $errors,
        private readonly int $status,
        ?Throwable $previous = null,
    ) {
        parent::__construct(previous: $previous);
    }

    #[Override]
    public function getErrorMessage(): string
    {
        return self::MESSAGE;
    }

    #[Override]
    public function getHttpCode(): int
    {
        return $this->status;
    }

    #[Override]
    public function getApiCode(): int
    {
        return $this->status;
    }

    #[Override]
    public function getErrors(): array
    {
        return $this->errors;
    }
}
