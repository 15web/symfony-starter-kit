<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

/**
 * Статусы ответов
 */
enum ResponseStatus: string
{
    /**
     * Успех
     */
    case Success = 'success';

    /**
     * Ошибка
     */
    case Error = 'error';
}
