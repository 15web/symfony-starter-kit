<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

/**
 * Данные профиля
 */
final class ProfileData
{
    public function __construct(
        public readonly ?string $phone = null,
        public readonly ?string $name = null
    ) {
    }
}
