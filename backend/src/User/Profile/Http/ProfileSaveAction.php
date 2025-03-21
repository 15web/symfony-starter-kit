<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Profile\Command\SaveProfile\SaveProfile;
use App\User\Profile\Command\SaveProfile\SaveProfileCommand;
use App\User\Profile\Query\FindByUserId\FindProfileByUserId;
use App\User\Profile\Query\FindByUserId\FindProfileByUserIdQuery;
use App\User\Profile\Query\FindByUserId\ProfileData;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка сохранения профиля
 */
#[IsGranted(UserRole::User)]
#[Route('/profile', methods: [Request::METHOD_POST])]
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
    ): ApiObjectResponse {
        ($this->saveProfile)(
            command: $command,
            userId: $userId,
        );

        ($this->flush)();

        return new ApiObjectResponse(
            data: $this->buildResponseData($userId),
        );
    }

    private function buildResponseData(UserId $userId): ProfileData
    {
        return ($this->findProfileByUserId)(
            query: new FindProfileByUserIdQuery(
                userId: $userId->value,
            ),
        );
    }
}
