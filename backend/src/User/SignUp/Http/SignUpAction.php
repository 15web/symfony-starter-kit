<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\SignUp\Command\SignUp;
use App\User\SignUp\Command\SignUpCommand;
use App\User\User\Domain\Exception\UserAlreadyExistException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка регистрации
 */
#[Route('/sign-up', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SignUpAction
{
    public function __construct(
        private SignUp $signUp,
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SignUpCommand $signUpCommand,
    ): ApiObjectResponse {
        try {
            ($this->signUp)($signUpCommand);
            ($this->flush)();
        } catch (UserAlreadyExistException) {
            throw new ApiBadResponseException(
                errors: ['Email уже занят, невозможно создать пользователя с такой почтой'],
                apiCode: ApiErrorCode::UserAlreadyExist,
            );
        }

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
