<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Symfony\Component\HttpFoundation\Response;

/**
 * Ошибка 404
 */
final class ApiNotFoundException extends \Exception implements ApiException
{
    public function __construct(private readonly string $errorMessage = 'Запись не найдена')
    {
        parent::__construct();
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getHttpCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getApiCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
