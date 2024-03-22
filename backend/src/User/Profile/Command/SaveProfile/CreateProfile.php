<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\AsService;
use App\Infrastructure\ValueObject\Phone;
use App\User\Profile\Domain\Profile;
use App\User\Profile\Domain\ProfileId;
use App\User\Profile\Domain\ProfileRepository;
use App\User\User\Domain\UserId;

/**
 * Создает профиль
 */
#[AsService]
final readonly class CreateProfile
{
    public function __construct(private ProfileRepository $profileRepository) {}

    public function __invoke(
        SaveProfileCommand $command,
        UserId $userId,
    ): void {
        $profile = new Profile(
            profileId: new ProfileId(),
            userId: $userId,
            phone: new Phone($command->phone),
            name: $command->name
        );

        $this->profileRepository->add($profile);
    }
}
