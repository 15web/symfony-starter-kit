<?php

declare(strict_types=1);

namespace App\User\Password\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\Password\Command\RecoverPassword;
use App\User\Password\Command\RecoverPasswordCommand;
use App\User\Password\Domain\RecoveryTokenRepository;
use App\User\User\Domain\Exception\UserNotFoundException;
use App\User\User\Domain\UserTokenRepository;
use SensitiveParameter;
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
    public function __construct(
        private RecoverPassword $recoverPassword,
        private Flush $flush,
        private RecoveryTokenRepository $recoveryTokenRepository,
        private UserTokenRepository $userTokenRepository,
    ) {}

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)]
        #[SensitiveParameter]
        Uuid $recoveryToken,
        #[ValueResolver(ApiRequestValueResolver::class)]
        RecoverPasswordCommand $recoverPasswordCommand,
    ): ApiObjectResponse {
        try {
            $token = $this->recoveryTokenRepository->findByToken($recoveryToken);

            if ($token === null) {
                throw new ApiNotFoundException(['Токен восстановления пароля не найден']);
            }

            ($this->recoverPassword)(
                recoveryToken: $token,
                recoverPasswordCommand: $recoverPasswordCommand,
            );

            $this->recoveryTokenRepository->remove($token);

            $this->userTokenRepository->removeAllByUserId($token->getUserId());

            ($this->flush)();
        } catch (UserNotFoundException) {
            throw new ApiNotFoundException(['Пользователь не найден']);
        }

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
