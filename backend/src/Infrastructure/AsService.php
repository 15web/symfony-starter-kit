<?php

declare(strict_types=1);

namespace App\Infrastructure;

/**
 * Классы, помеченные этим атрибутом, являются сервисами в DI
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsService
{
}
