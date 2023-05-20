<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use Webmozart\Assert\Assert;

/**
 * Объект запроса на создание статьи
 */
final readonly class CreateRequest
{
    public function __construct(
        public string $title,
        public string $alias,
        public string $body = '',
    ) {
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::notEmpty($alias, 'Укажите алиас');
    }
}
