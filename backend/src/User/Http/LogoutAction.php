<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\SuccessResponse;
use App\User\Domain\User;
use App\User\Domain\UserTokens;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/logout', methods: ['GET'])]
#[AsController]
final class LogoutAction
{
    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(#[CurrentUser] ?User $user, Request $request): SuccessResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        $apiToken = $request->headers->get(ApiTokenAuthenticator::TOKEN_NAME);
        if ($apiToken === null) {
            throw new ApiUnauthorizedException('Отсутствует токен в заголовках');
        }

        $userToken = $this->userTokens->findById(Uuid::fromString($apiToken));
        if ($userToken === null) {
            throw new ApiUnauthorizedException('Токен не найден');
        }

        $this->userTokens->remove($userToken);
        $this->entityManager->flush();

        return new SuccessResponse();
    }
}
