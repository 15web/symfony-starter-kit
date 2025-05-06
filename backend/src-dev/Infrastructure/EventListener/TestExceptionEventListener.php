<?php

declare(strict_types=1);

namespace Dev\Infrastructure\EventListener;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Обработка исключений в тестовом окружении
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 0)]
#[When('test')]
final readonly class TestExceptionEventListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if ($e instanceof ValidationFailed) {
            throw $e;
        }
    }
}
