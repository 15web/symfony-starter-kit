<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Infrastructure\Phone;
use App\User\Profile\Domain\Profile;
use App\User\Profile\Domain\ProfileId;
use App\User\Profile\Domain\Profiles;
use App\User\SignUp\Domain\UserId;

/**
 * Создает профиль
 */
#[AsService]
final readonly class CreateProfile
{
    public function __construct(private Profiles $profiles)
    {
    }

    public function __invoke(#[ApiRequest] SaveProfileCommand $command, UserId $userId): void
    {
        $profile = new Profile(
            new ProfileId(),
            $userId,
            new Phone($command->phone),
            $command->name
        );

        $this->profiles->add($profile);
    }
}
