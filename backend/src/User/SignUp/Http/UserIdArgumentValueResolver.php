<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\SignIn\Http\Auth\TokenException;
use App\User\SignIn\Http\Auth\TokenManager;
use App\User\SignUp\Domain\UserId;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Резолвер для айди пользователя
 */
#[AsService]
final readonly class UserIdArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(
        private TokenManager $tokenManager,
    ) {}

    /**
     * @return iterable<UserId>
     *
     * @throws ApiUnauthorizedException
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserId::class) {
            return [];
        }

        try {
            $userToken = $this->tokenManager->getToken($request);
        } catch (TokenException) {
            throw new ApiUnauthorizedException(['Необходимо пройти аутентификацию']);
        }

        return [$userToken->getUserId()];
    }
}
