<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Auth;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Domain\Users;
use DomainException;
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
        private Users $users,
        private LoggerInterface $logger,
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

        try {
            $user = $this->users->getById($userToken->getUserId());
        } catch (DomainException) {
            $this->logger->info('Пользователь токена не найден', [
                'userId' => $userToken->getUserId()->value,
                self::class => __FUNCTION__,
            ]);

            throw new ApiUnauthorizedException(['Пользователь не найден']);
        }

        $roleValues = array_map(static fn (UserRole $role) => $role->value, $user->getRoles());

        if (!\in_array($attribute->userRole->value, $roleValues, true)) {
            $this->logger->info('Доступ запрещен', [
                'userId' => $user->getUserId()->value,
                'role' => $attribute->userRole->value,
                self::class => __FUNCTION__,
            ]);

            throw new ApiUnauthorizedException(['Доступ запрещен']);
        }
    }
}
