<?php

declare(strict_types=1);

namespace App\User\Security\Http;

use App\Infrastructure\AsService;
use App\User\Security\Service\CheckRoleGranted;
use Override;
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
        private CheckRoleGranted $checkRoleGranted
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

        ($this->checkRoleGranted)($request, $attribute->userRole);
    }
}
