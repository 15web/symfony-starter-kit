<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

/**
 * Обычный ответ от сервера
 */
final readonly class ApiObjectResponse implements ApiResponse
{
    public function __construct(
        public object $data,
        public ResponseStatus $status = ResponseStatus::Success
    ) {}
}
