<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\RecoveryPassword\Command\RecoverPassword;
use App\User\RecoveryPassword\Command\RecoverPasswordCommand;
use App\User\RecoveryPassword\Command\RecoveryTokenNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка восстановления пароля
 */
#[Route('/recover-password/{recoveryToken}', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class RecoverPasswordAction
{
    public function __construct(private RecoverPassword $recoverPassword, private Flush $flush)
    {
    }

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)] Uuid $recoveryToken,
        #[ValueResolver(ApiRequestValueResolver::class)] RecoverPasswordCommand $recoverPasswordCommand,
    ): SuccessResponse {
        try {
            ($this->recoverPassword)(
                recoveryToken: $recoveryToken,
                recoverPasswordCommand: $recoverPasswordCommand,
            );

            ($this->flush)();
        } catch (RecoveryTokenNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
