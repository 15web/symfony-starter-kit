<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\User\Command\CreateToken;
use App\User\Model\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/sign-in', name: JsonLoginAuthenticator::SIGN_IN, methods: JsonLoginAuthenticator::SIGN_IN_METHODS)]
final class SignInAction
{
    public function __construct(private readonly CreateToken $createToken)
    {
    }

    public function __invoke(#[CurrentUser] ?User $user): UserResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        $token = ($this->createToken)($user);

        return new UserResponse($token->getId(), $user->getUserEmail()->getValue());
    }
}
