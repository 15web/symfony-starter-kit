<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ошибка 404
 */
final class ApiNotFoundException extends Exception implements ApiException
{
    private const MESSAGE = 'Запись не найдена';

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
        return Response::HTTP_NOT_FOUND;
    }

    public function getApiCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
