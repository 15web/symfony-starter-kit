<?php

declare(strict_types=1);

namespace App\Setting\Domain;

use Exception;

/**
 * Настройка не найдена
 */
final class SettingNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(message: 'Настройка не найдена');
    }
}
