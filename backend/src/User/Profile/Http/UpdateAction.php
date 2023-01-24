<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Flush;
use App\User\Profile\Domain\Profile;
use App\User\Profile\Domain\Profiles;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profile/{userId}/update', methods: ['POST'])]
#[AsController]
final class UpdateAction
{
    public function __construct(private readonly Profiles $profiles, private readonly Flush $flush)
    {
    }

    public function __invoke(Profile $user, UpdateRequest $updateRequest): Profile
    {
        $requestUser = $this->profiles->findById($updateRequest->uid);
        if ($requestUser->getId() !== $user->getId()) {
            throw new ApiBadResponseException('Пользователя с таким uid не существует', ApiErrorCode::ArticleAlreadyExist);
        }
        $user->change($updateRequest->name, $updateRequest->phone);

        ($this->flush)();

        return $user;
    }
}
