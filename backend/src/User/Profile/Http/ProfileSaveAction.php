<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\Flush;
use App\User\Profile\Command\SaveProfile\SaveProfile;
use App\User\Profile\Command\SaveProfile\SaveProfileCommand;
use App\User\Profile\Query\FindByUserId\FindProfileByUserId;
use App\User\Profile\Query\FindByUserId\FindProfileByUserIdQuery;
use App\User\Profile\Query\FindByUserId\ProfileData;
use App\User\SignUp\Domain\UserId;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка сохранения профиля
 */
#[IsGranted('ROLE_USER')]
#[Route('/profile-save', methods: ['POST'])]
#[AsController]
final class ProfileSaveAction
{
    public function __construct(
        private readonly SaveProfile $saveProfile,
        private readonly FindProfileByUserId $findProfileByUserId,
        private readonly Flush $flush,
    ) {
    }

    public function __invoke(SaveProfileCommand $command, UserId $userId): ProfileData
    {
        try {
            ($this->saveProfile)($command, $userId);

            ($this->flush)();
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return ($this->findProfileByUserId)(new FindProfileByUserIdQuery($userId->value));
    }
}
