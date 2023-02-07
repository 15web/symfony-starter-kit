<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

/**
 * Список кодов ошибок в АПИ
 */
enum ApiErrorCode: int
{
    case UserAlreadyExist = 1;

    case ArticleAlreadyExist = 2;

    case EmailAlreadyIsConfirmed = 3;

    case EmailIsNotConfirmed = 4;

    case NotFoundTasksForExport = 5;
}
