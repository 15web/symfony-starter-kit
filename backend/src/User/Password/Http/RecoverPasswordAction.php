<?php

declare(strict_types=1);

namespace App\User\Password\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\Password\Command\RecoverPassword;
use App\User\Password\Command\RecoverPasswordCommand;
use App\User\Password\Command\RecoveryTokenNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка восстановления пароля
 */
#[Route('/recover-password/{recoveryToken}', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class RecoverPasswordAction
{
    public function __construct(private RecoverPassword $recoverPassword, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)]
        Uuid $recoveryToken,
        #[ValueResolver(ApiRequestValueResolver::class)]
        RecoverPasswordCommand $recoverPasswordCommand,
    ): ApiObjectResponse {
        try {
            ($this->recoverPassword)(
                recoveryToken: $recoveryToken,
                recoverPasswordCommand: $recoverPasswordCommand,
            );

            ($this->flush)();
        } catch (RecoveryTokenNotFoundException) {
            throw new ApiNotFoundException(['Токен восстановления пароля не найден']);
        }

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
