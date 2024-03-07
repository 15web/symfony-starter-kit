<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Override;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ошибка аутентификации
 */
final class ApiUnauthorizedException extends Exception implements ApiException
{
    private const string MESSAGE = 'Необходимо пройти аутентификацию';

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct();
    }

    #[Override]
    public function getErrorMessage(): string
    {
        return self::MESSAGE;
    }

    #[Override]
    public function getHttpCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    #[Override]
    public function getApiCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    #[Override]
    public function getErrors(): array
    {
        return $this->errors;
    }
}
