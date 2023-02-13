<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Infrastructure\Phone;
use App\User\Profile\Domain\Profile;
use App\User\Profile\Domain\ProfileId;
use App\User\Profile\Domain\Profiles;
use App\User\SignUp\Domain\UserId;

/**
 * Хендлер сохранения профиля
 */
#[AsService]
final class SaveProfile
{
    public function __construct(
        private readonly Flush $flush,
        private readonly Profiles $profiles,
    ) {
    }

    public function __invoke(SaveProfileCommand $command, UserId $userId): void
    {
        $profile = $this->profiles->findByUserId($userId);

        if ($profile !== null) {
            $this->update($command, $profile);

            return;
        }

        $this->add($command, $userId);
    }

    private function add(SaveProfileCommand $command, UserId $userId): void
    {
        $profile = new Profile(
            new ProfileId(),
            $userId,
            new Phone($command->phone),
            $command->name
        );

        $this->profiles->add($profile);
        ($this->flush)();
    }

    private function update(SaveProfileCommand $command, Profile $profile): void
    {
        $profile->changeName($command->name);
        $profile->changePhone(new Phone($command->phone));

        ($this->flush)();
    }
}
