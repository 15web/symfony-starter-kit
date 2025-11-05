<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\CheckRateLimiter;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\SignIn\Command\SignIn;
use App\User\SignIn\Command\SignInCommand;
use App\User\User\Domain\AuthToken;
use App\User\User\Domain\Exception\EmailIsNotConfirmedException;
use DomainException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка аутентификации
 */
#[Route('/sign-in', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SignInAction
{
    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private SignIn $signIn,
        private RateLimiterFactoryInterface $signInLimiter,
        private CheckRateLimiter $checkRateLimiter,
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(RequestValueResolver::class)]
        Request $request,
        #[ValueResolver(ApiRequestValueResolver::class)]
        SignInRequest $signInRequest,
    ): ApiObjectResponse {
        /** @var non-empty-string|null $rateLimiterKey */
        $rateLimiterKey = $request->getClientIp();

        $limiter = ($this->checkRateLimiter)(
            rateLimiter: $this->signInLimiter,
            key: $rateLimiterKey,
        );

        $token = AuthToken::generate(
            hashCost: $this->hashCost,
        );

        try {
            ($this->signIn)(new SignInCommand(
                email: $signInRequest->email,
                password: $signInRequest->password,
                authToken: $token,
            ));

            ($this->flush)();
        } catch (EmailIsNotConfirmedException) {
            throw new ApiBadResponseException(
                errors: ['Email не подтвержден'],
                apiCode: ApiErrorCode::EmailIsNotConfirmed,
            );
        } catch (DomainException) {
            throw new ApiBadResponseException(
                errors: ['Неверно указан логин или пароль'],
                apiCode: ApiErrorCode::Unauthenticated,
            );
        }

        $limiter->reset();

        return new ApiObjectResponse(
            data: new UserTokenData(
                (string) $token,
            ),
        );
    }
}
