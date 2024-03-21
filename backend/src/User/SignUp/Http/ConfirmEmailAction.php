<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\SignUp\Command\ConfirmEmail;
use App\User\User\Domain\Exception\EmailAlreadyIsConfirmedException;
use App\User\User\Domain\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка подтверждения почты
 */
#[Route('/confirm-email/{confirmToken}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ConfirmEmailAction
{
    public function __construct(
        private ConfirmEmail $confirmEmail,
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)]
        Uuid $confirmToken,
    ): ApiObjectResponse {
        try {
            ($this->confirmEmail)($confirmToken);
            ($this->flush)();
        } catch (EmailAlreadyIsConfirmedException) {
            throw new ApiBadResponseException(
                errors: ['Нельзя подтвердить уже подтвержденный email'],
                apiCode: ApiErrorCode::EmailAlreadyIsConfirmed,
            );
        } catch (UserNotFoundException) {
            throw new ApiNotFoundException(['Пользователь не найден']);
        }

        return new ApiObjectResponse(
            data: new SuccessResponse()
        );
    }
}
