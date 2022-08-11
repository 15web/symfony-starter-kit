<?php

declare(strict_types=1);

namespace App\User\Command;

use App\User\Domain\User;
use App\User\Domain\UserToken;
use App\User\Domain\UserTokens;
use Doctrine\ORM\EntityManagerInterface;

final class CreateToken
{
    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): UserToken
    {
        $token = new UserToken($user);

        $this->userTokens->add($token);

        $this->entityManager->flush();

        return $token;
    }
}
