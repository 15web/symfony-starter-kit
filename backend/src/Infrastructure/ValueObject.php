<?php

declare(strict_types=1);

namespace App\Infrastructure;

interface ValueObject
{
    public function equalTo(self $other): bool;
}
