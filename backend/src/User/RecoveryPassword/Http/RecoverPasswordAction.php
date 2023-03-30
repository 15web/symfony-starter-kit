<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\RecoveryPassword\Command\RecoverPassword;
use App\User\RecoveryPassword\Command\RecoverPasswordCommand;
use App\User\RecoveryPassword\Command\RecoveryTokenNotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка восстановления пароля
 */
#[Route('/recover-password/{recoveryToken}', methods: ['POST'])]
#[AsController]
final readonly class RecoverPasswordAction
{
    public function __construct(private RecoverPassword $recoverPassword, private Flush $flush)
    {
    }

    public function __invoke(Uuid $recoveryToken, RecoverPasswordCommand $recoverPasswordCommand): SuccessResponse
    {
        try {
            ($this->recoverPassword)($recoveryToken, $recoverPasswordCommand);

            ($this->flush)();
        } catch (RecoveryTokenNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}