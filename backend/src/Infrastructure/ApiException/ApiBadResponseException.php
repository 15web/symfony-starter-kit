<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Override;
use Symfony\Component\HttpFoundation\Response;

/**
 * Класс исключения для ошибочного ответа
 */
final class ApiBadResponseException extends Exception implements ApiException
{
    private const string MESSAGE = 'Ошибка';

    /**
     * @param non-empty-list<non-empty-string> $errors
     */
    public function __construct(private readonly array $errors, private readonly ApiErrorCode $apiCode)
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
        return Response::HTTP_OK;
    }

    #[Override]
    public function getApiCode(): int
    {
        return $this->apiCode->value;
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
