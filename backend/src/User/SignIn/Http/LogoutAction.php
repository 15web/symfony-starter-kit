<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\SignIn\Command\DeleteToken;
use App\User\SignIn\Http\Authenticator\ApiTokenAuthenticator;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка выхода из системы
 */
#[IsGranted(UserRole::User->value)]
#[Route('/logout', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class LogoutAction
{
    public function __construct(
        private DeleteToken $deleteToken,
        private Flush $flush,
    ) {
    }

    public function __invoke(
        #[ValueResolver(RequestValueResolver::class)] Request $request,
    ): SuccessResponse {
        $apiToken = $request->headers->get(ApiTokenAuthenticator::TOKEN_NAME);
        if ($apiToken === null) {
            throw new ApiUnauthorizedException('Отсутствует токен в заголовках');
        }

        ($this->deleteToken)(Uuid::fromString($apiToken));
        ($this->flush)();

        return new SuccessResponse();
    }
}
