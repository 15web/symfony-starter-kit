<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\Security\Authenticator\JsonLoginAuthenticator;
use App\Infrastructure\Security\UserProvider\SecurityUser;
use App\User\Command\CreateToken;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sign-in', name: JsonLoginAuthenticator::SIGN_IN, methods: JsonLoginAuthenticator::SIGN_IN_METHODS)]
#[AsController]
final class SignInAction
{
    public function __construct(private readonly CreateToken $createToken)
    {
    }

    public function __invoke(SecurityUser $securityUser): UserResponse
    {
        $token = ($this->createToken)($securityUser->getId());

        return new UserResponse($token, $securityUser->getUserIdentifier());
    }
}
