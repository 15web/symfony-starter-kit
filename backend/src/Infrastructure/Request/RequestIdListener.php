<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\AsService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Uid\UuidV7;

/**
 * Добавление идентификатора запроса в заголовки
 */
#[AsService]
final readonly class RequestIdListener
{
    public const string TRACE_ID_HEADER = 'X-Request-TraceId';

    public const array IGNORED_ROUTES = [
        'ping',
    ];

    #[AsEventListener(priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->shouldBeTraced($event)) {
            return;
        }

        $traceId = (string) new UuidV7();
        $request = $event->getRequest();

        if (!$request->headers->has(self::TRACE_ID_HEADER)) {
            $request->headers->set(
                key: self::TRACE_ID_HEADER,
                values: $traceId,
            );
        }
    }

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($response->headers->has(self::TRACE_ID_HEADER)) {
            return;
        }

        if (!$request->headers->has(self::TRACE_ID_HEADER)) {
            return;
        }

        $response->headers->set(
            key: self::TRACE_ID_HEADER,
            values: $request->headers->get(self::TRACE_ID_HEADER),
        );
    }

    private function shouldBeTraced(KernelEvent $event): bool
    {
        if (!$event->isMainRequest()) {
            return false;
        }

        $request = $event->getRequest();

        /** @var non-empty-string|null $routeName */
        $routeName = $request->attributes->get('_route');
        if (\in_array($routeName, self::IGNORED_ROUTES, true)) {
            return false;
        }

        return str_starts_with(
            haystack: $request->getPathInfo(),
            needle: '/api',
        );
    }
}
