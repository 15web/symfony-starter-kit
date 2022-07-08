<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\User\Command\CreateToken;
use App\User\Model\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/sign-in', name: 'sign-in', methods: ['POST'])]
final class SignInAction
{
    public function __construct(private readonly CreateToken $createToken)
    {
    }

    public function __invoke(#[CurrentUser] ?User $user): UserResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException('Необходимо пройти аутентификацию');
        }

        $token = ($this->createToken)($user);

        return new UserResponse($token->getId(), $user->getUserEmail()->getValue());
    }
}
