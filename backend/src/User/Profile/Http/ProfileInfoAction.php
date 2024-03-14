<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Profile\Query\FindByUserId\FindProfileByUserId;
use App\User\Profile\Query\FindByUserId\FindProfileByUserIdQuery;
use App\User\Profile\Query\FindByUserId\ProfileData;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use App\User\User\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка получения информации о профиле
 */
#[IsGranted(UserRole::User)]
#[Route('/profile', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ProfileInfoAction
{
    public function __construct(private FindProfileByUserId $findProfileByUserId) {}

    public function __invoke(
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId
    ): ApiObjectResponse {
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
