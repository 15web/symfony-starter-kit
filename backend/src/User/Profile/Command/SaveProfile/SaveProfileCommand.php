<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use SensitiveParameter;

/**
 * Команда сохранения профиля
 */
final readonly class SaveProfileCommand
{
    /**
     * @param non-empty-string $phone
     * @param non-empty-string $name
     */
    public function __construct(
        #[SensitiveParameter]
        public string $phone,
        public string $name,
    ) {}
}
