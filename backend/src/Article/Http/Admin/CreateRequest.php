<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

final class CreateRequest implements ApiRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
        public readonly string $body = '',
    ) {
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::notEmpty($alias, 'Укажите алиас');
    }
}
