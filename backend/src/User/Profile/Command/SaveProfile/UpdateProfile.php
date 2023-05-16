<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Infrastructure\Phone;
use App\User\Profile\Domain\Profile;

/**
 * Обновляет профиль
 */
#[AsService]
final class UpdateProfile
{
    public function __invoke(#[ApiRequest] SaveProfileCommand $command, Profile $profile): void
    {
        $profile->changeName($command->name);
        $profile->changePhone(new Phone($command->phone));
    }
}
