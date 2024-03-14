<?php

declare(strict_types=1);

namespace App\User\User\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Аутентификация и авторизация пользователя по токену для контроллеров с атрибутом IsGranted.
 */
#[AsService]
final readonly class IsGrantedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenManager $tokenManager,
        private LoggerInterface $logger,
        private FindUser $findUser,
    ) {}

    /**
     * @return array<string, array{0: string, 1: int}|list<array{0: string, 1?: int}>|string>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 1000],
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        /** @var IsGranted|null $attribute */
        $attribute = $event->getAttributes()[IsGranted::class][0] ?? null;

        if ($attribute === null) {
            return;
        }

        $request = $event->getRequest();

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

        if ($attribute->userRole !== $userData->role) {
            $this->logger->info('Доступ запрещен', [
                'userId' => $userData->userId,
                'role' => $attribute->userRole->value,
                self::class => __FUNCTION__,
            ]);

            throw new ApiUnauthorizedException(['Доступ запрещен']);
        }
    }
}
