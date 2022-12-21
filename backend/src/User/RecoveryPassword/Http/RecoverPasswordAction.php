<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\SuccessResponse;
use App\User\RecoveryPassword\Command\RecoverPassword;
use App\User\RecoveryPassword\Command\RecoverPasswordCommand;
use App\User\SignUp\Domain\UserNotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recover-password', methods: ['POST'])]
#[AsController]
final class RecoverPasswordAction
{
    public function __construct(private readonly RecoverPassword $recoverPassword)
    {
    }

    public function __invoke(RecoverPasswordCommand $recoverPasswordCommand): SuccessResponse
    {
        try {
            ($this->recoverPassword)($recoverPasswordCommand);
        } catch (UserNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
