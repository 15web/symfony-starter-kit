<?php

declare(strict_types=1);

namespace App\User\Security\Service;

use App\Infrastructure\ApiException\ApiAccessForbiddenException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\User\User\Domain\UserRole;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Сервис для проверки наличия роли у пользователя
 */
final readonly class CheckRoleGranted
{
    public function __construct(
        private TokenManager $tokenManager,
        private LoggerInterface $logger,
        private FindUser $findUser,
    ) {}

    public function __invoke(Request $request, UserRole $role): void
    {
        try {
            $userToken = $this->tokenManager->getToken($request);
        } catch (TokenException) {
            throw new ApiUnauthorizedException(['Невалидный токен']);
        }

        $userData = ($this->findUser)(
            new FindUserQuery(userId: $userToken->getUserId())
        );

        if ($userData === null) {
            $this->logger->info('Пользователь токена не найден', [
                'userId' => $userToken->getUserId()->value,
                self::class => __FUNCTION__,
            ]);

            throw new ApiUnauthorizedException(['Пользователь не найден']);
        }

        // Администратор имеет доступ ко всем ручкам, требующим авторизацию
        if ($userData->role === UserRole::Admin) {
            return;
        }

        if ($role !== $userData->role) {
            $this->logger->info('Доступ запрещен', [
                'userId' => $userData->userId,
                'role' => $role->value,
                self::class => __FUNCTION__,
            ]);

            throw new ApiAccessForbiddenException(['Доступ запрещен']);
        }
    }
}
