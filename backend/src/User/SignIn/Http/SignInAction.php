<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\SignIn\Command\CreateToken;
use App\User\SignIn\Http\Authenticator\JsonLoginAuthenticator;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
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
    ) {}

    public function __invoke(
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId
    ): ApiObjectResponse {
        $token = new UuidV7();
        ($this->createToken)(
            userId: $userId,
            userTokenId: $token,
        );

        ($this->flush)();

        return new ApiObjectResponse(
            data: new UserResponse($token),
        );
    }
}
