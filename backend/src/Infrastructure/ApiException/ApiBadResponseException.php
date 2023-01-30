<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Symfony\Component\HttpFoundation\Response;

/**
 * Класс исключения для ошибочного ответа
 */
final class ApiBadResponseException extends \Exception implements ApiException
{
    public function __construct(private readonly string $errorMessage, private readonly ApiErrorCode $apiCode)
    {
        parent::__construct();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getHttpCode(): int
    {
        return Response::HTTP_OK;
    }

    public function getApiCode(): int
    {
        return $this->apiCode->value;
    }
}
