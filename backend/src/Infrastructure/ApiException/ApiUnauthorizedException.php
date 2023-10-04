<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ошибка аутентификации
 */
final class ApiUnauthorizedException extends Exception implements ApiException
{
    private const MESSAGE = 'Необходимо пройти аутентификацию';

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct();
    }

    public function getErrorMessage(): string
    {
        return self::MESSAGE;
    }

    public function getHttpCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getApiCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
