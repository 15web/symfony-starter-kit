<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiRequestResolver;

use Attribute;

/**
 * Классы, помеченные этим атрибутом, являются объектами запросов
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ApiRequest
{
}
