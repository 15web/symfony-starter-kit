<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\User\Profile\Query\FindByUserId\FindProfileByUserId;
use App\User\Profile\Query\FindByUserId\FindProfileByUserIdQuery;
use App\User\Profile\Query\FindByUserId\ProfileData;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка получения информации о профиле
 */
#[IsGranted('ROLE_USER')]
#[Route('/profile', methods: ['GET'])]
#[AsController]
final class ProfileInfoAction
{
    public function __construct(private readonly FindProfileByUserId $findProfileByUserId)
    {
    }

    public function __invoke(UserId $userId): ProfileData
    {
        return ($this->findProfileByUserId)(new FindProfileByUserIdQuery($userId->value));
    }
}
