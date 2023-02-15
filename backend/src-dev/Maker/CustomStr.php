<?php

declare(strict_types=1);

namespace Dev\Maker;

use Symfony\Bundle\MakerBundle\Str;

/**
 * Генерирует рандомные имена (для примера, как make:entity) для создаваемого модуля
 */
final class CustomStr
{
    public static function asClassName(string $value, string $suffix = ''): string
    {
        return Str::asClassName($value, $suffix);
    }

    public static function getRandomTerm(): string
    {
        $adjectives = [
            'article',
            'post',
            'order',
        ];

        return sprintf('%s %s', $adjectives[array_rand($adjectives)], '');
    }
}
