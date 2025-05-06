<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\User\Profile\Domain\ProfileRepository;
use App\User\User\Domain\UserId;

/**
 * Хендлер сохранения профиля
 */
final readonly class SaveProfile
{
    public function __construct(
        private CreateProfile $createProfile,
        private UpdateProfile $updateProfile,
        private ProfileRepository $profileRepository,
    ) {}

    public function __invoke(
        SaveProfileCommand $command,
        UserId $userId,
    ): void {
        $profile = $this->profileRepository->findByUserId($userId);

        if ($profile !== null) {
            ($this->updateProfile)(
                command: $command,
                profile: $profile,
            );

            return;
        }

        ($this->createProfile)(
            command: $command,
            userId: $userId,
        );
    }
}
