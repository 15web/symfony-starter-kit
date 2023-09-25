<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

/**
 * Данные профиля
 */
final readonly class ProfileData
{
    public function __construct(
        public ?string $phone = null,
        public ?string $name = null
    ) {}
}
