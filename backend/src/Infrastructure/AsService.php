<?php

declare(strict_types=1);

namespace App\Infrastructure;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsService
{
    public function __construct()
    {
    }
}
