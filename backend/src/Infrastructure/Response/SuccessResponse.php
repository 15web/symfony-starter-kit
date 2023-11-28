<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

/**
 * Дефолтный успешный ответ
 */
final readonly class SuccessResponse
{
    public ResponseStatus $status;

    public function __construct()
    {
        $this->status = ResponseStatus::Success;
    }
}
