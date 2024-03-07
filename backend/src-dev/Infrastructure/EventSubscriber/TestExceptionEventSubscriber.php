<?php

declare(strict_types=1);

namespace Dev\Infrastructure\EventSubscriber;

use App\Infrastructure\AsService;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Override;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Обработка исключений в тестовом окружении
 */
#[AsService]
#[When('test')]
final readonly class TestExceptionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, array{0: string, 1: int}|list<array{0: string, 1?: int}>|string>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleException', 0],
            ],
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if ($e instanceof ValidationFailed) {
            throw $e;
        }
    }
}
