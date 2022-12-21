<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\SuccessResponse;
use App\User\RecoveryPassword\Command\SendRecoverPassword;
use App\User\RecoveryPassword\Command\SendRecoverPasswordCommand;
use App\User\SignUp\Domain\UserNotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recover-password-send', methods: ['POST'])]
#[AsController]
final class SendRecoverPasswordAction
{
    public function __construct(private readonly SendRecoverPassword $sendRecoverPassword)
    {
    }

    public function __invoke(SendRecoverPasswordCommand $sendRecoverPasswordCommand): SuccessResponse
    {
        try {
            ($this->sendRecoverPassword)($sendRecoverPasswordCommand);
        } catch (UserNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
