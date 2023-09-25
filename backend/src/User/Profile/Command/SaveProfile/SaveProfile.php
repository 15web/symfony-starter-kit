<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\AsService;
use App\User\Profile\Domain\Profiles;
use App\User\SignUp\Domain\UserId;

/**
 * Хендлер сохранения профиля
 */
#[AsService]
final readonly class SaveProfile
{
    public function __construct(
        private CreateProfile $createProfile,
        private UpdateProfile $updateProfile,
        private Profiles $profiles,
    ) {}

    public function __invoke(
        SaveProfileCommand $command,
        UserId $userId,
    ): void {
        $profile = $this->profiles->findByUserId($userId);

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
