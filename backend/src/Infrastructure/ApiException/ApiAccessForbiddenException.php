<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Ошибка доступа
 */
final class ApiAccessForbiddenException extends Exception implements ApiException
{
    private const string MESSAGE = 'Доступ запрещен';

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(
        private readonly array $errors,
        ?Throwable $previous = null,
    ) {
        parent::__construct(previous: $previous);
    }

    #[Override]
    public function getHttpCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    #[Override]
    public function getApiCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    #[Override]
    public function getErrorMessage(): string
    {
        return self::MESSAGE;
    }

    #[Override]
    public function getErrors(): array
    {
        return $this->errors;
    }
}
