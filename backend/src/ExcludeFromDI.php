<?php

declare(strict_types=1);

namespace App;

#[ExcludeFromDI]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class ExcludeFromDI
{
    public function __construct()
    {
    }
}
