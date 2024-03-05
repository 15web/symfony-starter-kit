<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\SignIn\Command\DeleteToken;
use App\User\SignIn\Http\Auth\IsGranted;
use App\User\SignIn\Http\Auth\TokenManager;
use App\User\SignUp\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка выхода из системы
 */
#[IsGranted(UserRole::User)]
#[Route('/logout', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class LogoutAction
{
    public function __construct(
        private DeleteToken $deleteToken,
        private Flush $flush,
        private TokenManager $tokenManager,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(RequestValueResolver::class)]
        Request $request,
    ): ApiObjectResponse {
        $userToken = $this->tokenManager->getToken($request);

        ($this->deleteToken)($userToken->getId());

        ($this->flush)();

        $this->logger->info('Пользователь разлогинен', [
            'userId' => $userToken->getUserId()->value,
            self::class => __FUNCTION__,
        ]);

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
