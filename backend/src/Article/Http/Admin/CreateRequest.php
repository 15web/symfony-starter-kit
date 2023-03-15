<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Объект запроса на создание статьи
 */
final readonly class CreateRequest implements ApiRequest
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
