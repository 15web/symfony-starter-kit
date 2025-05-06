<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\ValueObject\Phone;
use App\User\Profile\Domain\Profile;

/**
 * Обновляет профиль
 */
final class UpdateProfile
{
    public function __invoke(
        SaveProfileCommand $command,
        Profile $profile,
    ): void {
        $profile->changeName($command->name);
        $profile->changePhone(new Phone($command->phone));
    }
}
