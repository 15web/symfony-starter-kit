<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\Security\Authenticator\JsonLoginAuthenticator;
use App\User\SignIn\Command\CreateToken;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/sign-in', name: JsonLoginAuthenticator::SIGN_IN, methods: JsonLoginAuthenticator::SIGN_IN_METHODS)]
#[AsController]
final class SignInAction
{
    public function __construct(private readonly CreateToken $createToken)
    {
    }

    public function __invoke(UserId $userId): UserResponse
    {
        $token = Uuid::v4();
        ($this->createToken)($userId, $token);

        return new UserResponse($token);
    }
}
