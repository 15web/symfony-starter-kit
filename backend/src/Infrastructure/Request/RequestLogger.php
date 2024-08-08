<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\AsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Логирование всех запросов и ответов
 */
#[AsService]
final readonly class RequestLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[AsEventListener]
    public function logRequest(RequestEvent $event): void
    {
        if (!$this->shouldBeLogged($event)) {
            return;
        }

        $error = null;
        $request = $event->getRequest();

        $message = sprintf('<<< %s %s', $request->getMethod(), $request->getRequestUri());

        try {
            $payload = $request->getPayload()->all();
        } catch (JsonException $e) {
            $error = $e->getMessage();
            $payload = $request->getContent();
        }

        $this->logger->info($message, [
            'payload' => $payload,
            'headers' => $this->collectHeaders($request->headers),
            'ip' => $request->getClientIp(),
            'error' => $error,
        ]);
    }

    #[AsEventListener]
    public function logResponse(ResponseEvent $event): void
    {
        if (!$this->shouldBeLogged($event)) {
            return;
        }

        $response = $event->getResponse();

        $message = sprintf('>>> %s', $response->getStatusCode());

        $this->logger->info($message, [
            'content' => (string) $response->getContent(),
            'headers' => $this->collectHeaders($response->headers),
        ]);
    }

    private function shouldBeLogged(KernelEvent $event): bool
    {
        if (!$event->isMainRequest()) {
            return false;
        }

        $request = $event->getRequest();

        /** @var non-empty-string|null $routeName */
        $routeName = $request->attributes->get('_route');
        if (\in_array($routeName, RequestIdListener::IGNORED_ROUTES, true)) {
            return false;
        }

        return str_starts_with(
            haystack: $request->getPathInfo(),
            needle: '/api',
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function collectHeaders(HeaderBag $headerBag): array
    {
        /** @var array<string, list<string|null>> $headers */
        $headers = $headerBag->all();

        $mapped = array_map(
            callback: static fn (array $header): ?string => $header[0],
            array: $headers,
        );

        if (\array_key_exists('authorization', $mapped)) {
            $mapped['authorization'] = '***';
        }

        return $mapped;
    }
}
