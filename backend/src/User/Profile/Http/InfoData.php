<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

final class InfoData
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
    ) {
    }
}
