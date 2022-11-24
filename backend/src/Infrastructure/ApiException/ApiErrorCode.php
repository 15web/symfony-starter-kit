<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

enum ApiErrorCode: int
{
    case UserAlreadyExist = 1;

    case ArticleAlreadyExist = 2;
}
