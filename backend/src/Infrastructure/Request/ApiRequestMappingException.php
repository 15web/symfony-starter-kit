<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use Exception;
use Throwable;

/**
 * Исключение при десериализации JSON -> DTO, последовательно используется в методах
 *      - ApiRequestMapper::registerUuidConstructor()
 *      - ApiRequestMapper::filterAllowedExceptions()
 */
final class ApiRequestMappingException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct(message: $previous->getMessage(), previous: $previous);
    }
}
