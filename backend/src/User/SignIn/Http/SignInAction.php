<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\ValueObject\Email;
use App\User\SignIn\Command\SignIn;
use App\User\SignIn\Command\SignInCommand;
use App\User\User\Domain\AuthToken;
use App\User\User\Domain\Exception\EmailIsNotConfirmedException;
use DomainException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
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
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SignInRequest $signInRequest,
    ): ApiObjectResponse {
        $token = AuthToken::generate(
            hashCost: $this->hashCost,
        );

        try {
            ($this->signIn)(new SignInCommand(
                email: new Email($signInRequest->email),
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
            throw new ApiUnauthorizedException(['Ошибка аутентификации']);
        }

        return new ApiObjectResponse(
            data: new UserTokenData(
                (string) $token,
            ),
        );
    }
}
