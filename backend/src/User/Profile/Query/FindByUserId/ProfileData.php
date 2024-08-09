<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

use SensitiveParameter;

/**
 * Данные профиля
 */
final readonly class ProfileData
{
    public function __construct(
        #[SensitiveParameter]
        public ?string $phone = null,
        public ?string $name = null
    ) {}
}
