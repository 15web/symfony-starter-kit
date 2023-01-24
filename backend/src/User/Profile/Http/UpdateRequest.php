<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final class UpdateRequest implements ApiRequest
{
    public function __construct(
        public readonly Uuid $uid,
        public readonly string $name,
        public readonly string $phone = ''
    ) {
        Assert::notEmpty($uid, 'Укажите Uid');
        Assert::notEmpty($name, 'Укажите Имя');
    }
}
