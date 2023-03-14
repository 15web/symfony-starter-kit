<?php

declare(strict_types=1);

namespace App\Infrastructure;

/**
 * Дефолтный успешный ответ
 */
final readonly class SuccessResponse
{
    private const SUCCESS_VALUE = true;

    public bool $success;

    public function __construct()
    {
        $this->success = self::SUCCESS_VALUE;
    }
}
