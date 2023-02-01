<?php

declare(strict_types=1);

namespace App\Infrastructure;

/**
 * Дефолтный успешный ответ
 */
final class SuccessResponse
{
    private const SUCCESS_VALUE = true;

    public readonly bool $success;

    public function __construct()
    {
        $this->success = self::SUCCESS_VALUE;
    }
}
