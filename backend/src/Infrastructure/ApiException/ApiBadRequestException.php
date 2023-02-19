<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Класс исключения для ошибочного запроса
 */
final class ApiBadRequestException extends Exception implements ApiException
{
    public function __construct(
        private readonly string $errorMessage = 'Укажите корректный запрос',
        ?Throwable $previous = null,
    ) {
        parent::__construct(previous: $previous);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getHttpCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getApiCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
