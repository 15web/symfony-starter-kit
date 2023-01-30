<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Symfony\Component\HttpFoundation\Response;

/**
 * Ошибка аутентификации
 */
final class ApiUnauthorizedException extends \Exception implements ApiException
{
    public function __construct(private readonly string $errorMessage = 'Необходимо пройти аутентификацию')
    {
        parent::__construct();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getHttpCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getApiCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }
}
