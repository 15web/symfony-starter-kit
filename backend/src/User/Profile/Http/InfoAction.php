<?php

declare(strict_types=1);

namespace App\User\Profile\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\User\Profile\Domain\Profiles;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/profile/{userId}/info', methods: ['GET'])]
#[AsController]
final class InfoAction
{
    public function __construct(private readonly Profiles $users)
    {
    }

    public function __invoke(Uuid $userId): InfoData
    {
        $user = $this->users->findById(Uuid::fromString((string) $userId));
        if ($user === null) {
            throw new ApiNotFoundException('Пользователь не найден');
        }

        return new InfoData($user->getName(), $user->getPhone());
    }
}
