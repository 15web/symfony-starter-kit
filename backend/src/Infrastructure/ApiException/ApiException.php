<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

interface ApiException
{
    public function getHttpCode(): int;

    public function getApiCode(): int;

    public function getErrorMessage(): string;
}
