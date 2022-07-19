<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Symfony\Component\HttpFoundation\Response;

final class ApiBadRequestException extends \Exception implements ApiException
{
    public function __construct(private readonly string $errorMessage = 'Укажите корректный запрос')
    {
        parent::__construct();
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
