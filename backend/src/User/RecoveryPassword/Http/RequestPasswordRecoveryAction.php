<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\RecoveryPassword\Command\GenerateRecoveryToken;
use App\User\RecoveryPassword\Command\GenerateRecoveryTokenCommand;
use App\User\SignUp\Domain\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка запроса на восстановление пароля
 */
#[Route('/request-password-recovery', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class RequestPasswordRecoveryAction
{
    public function __construct(private GenerateRecoveryToken $generateRecoveryToken, private Flush $flush)
    {
    }

    public function __invoke(GenerateRecoveryTokenCommand $command): SuccessResponse
    {
        try {
            ($this->generateRecoveryToken)($command);

            ($this->flush)();
        } catch (UserNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
