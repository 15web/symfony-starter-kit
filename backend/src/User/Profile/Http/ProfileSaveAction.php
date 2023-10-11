<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\User\Profile\Command\SaveProfile\SaveProfile;
use App\User\Profile\Command\SaveProfile\SaveProfileCommand;
use App\User\Profile\Query\FindByUserId\FindProfileByUserId;
use App\User\Profile\Query\FindByUserId\FindProfileByUserIdQuery;
use App\User\Profile\Query\FindByUserId\ProfileData;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка сохранения профиля
 */
#[IsGranted(UserRole::User->value)]
#[Route('/profile-save', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class ProfileSaveAction
{
    public function __construct(
        private SaveProfile $saveProfile,
        private FindProfileByUserId $findProfileByUserId,
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SaveProfileCommand $command,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ProfileData {
        ($this->saveProfile)(
            command: $command,
            userId: $userId,
        );

        ($this->flush)();

        return ($this->findProfileByUserId)(
            query: new FindProfileByUserIdQuery(
                userId: $userId->value,
            ),
        );
    }
}
