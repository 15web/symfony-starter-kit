<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\Flush;
use App\User\SignIn\Command\CreateToken;
use App\User\SignIn\Http\Authenticator\JsonLoginAuthenticator;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\UuidV7;

/**
 * Ручка аутентификации
 */
#[Route('/sign-in', name: JsonLoginAuthenticator::SIGN_IN, methods: JsonLoginAuthenticator::SIGN_IN_METHODS)]
#[AsController]
final readonly class SignInAction
{
    public function __construct(
        private CreateToken $createToken,
        private Flush $flush,
    ) {
    }

    public function __invoke(UserId $userId): UserResponse
    {
        $token = new UuidV7();
        ($this->createToken)($userId, $token);

        ($this->flush)();

        return new UserResponse($token);
    }
}
