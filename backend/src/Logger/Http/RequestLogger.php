<?php

declare(strict_types=1);

namespace App\Logger\Http;

use App\User\Security\Http\IsGranted;
use App\User\Security\Service\TokenException;
use App\User\Security\Service\TokenManager;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Normalizer\ArrayNormalizer;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Логирование всех запросов и ответов
 */
final class RequestLogger
{
    /**
     * @var ReflectionClass<object>|null
     */
    private ?ReflectionClass $controllerReflection;

    private readonly ArrayNormalizer $normalizer;

    public function __construct(
        private readonly TokenManager $tokenManager,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger,
        NormalizerBuilder $builder,
    ) {
        $this->controllerReflection = null;
        $this->normalizer = $builder->normalizer(Format::array());
    }

    #[AsEventListener]
    public function logRequest(RequestEvent $event): void
    {
        if (!$this->shouldBeLogged($event)) {
            return;
        }

        $error = null;
        $request = $event->getRequest();

        $message = \sprintf('<<< %s %s', $request->getMethod(), $request->getRequestUri());

        try {
            $payload = $request->getPayload()->all();
        } catch (JsonException $e) {
            $error = $e->getMessage();
            $payload = $request->getContent();
        }

        $context = [
            'payload' => $payload,
            'headers' => $this->collectHeaders($request->headers),
            'ip' => $request->getClientIp(),
            'error' => $error,
        ];

        $this->setControllerReflection($request);

        // Запись ID авторизованного пользователя
        if ($this->shouldLogUser()) {
            $context['userId'] = $this->getUserId($request);
        }

        $this->logger->info($message, $context);
    }

    #[AsEventListener]
    public function logResponse(ResponseEvent $event): void
    {
        if (!$this->shouldBeLogged($event)) {
            return;
        }

        $response = $event->getResponse();

        $message = \sprintf('>>> %s', $response->getStatusCode());

        $this->logger->info($message, [
            'content' => $this->prepareResponseContent($response),
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

    /**
     * @return array<array-key, mixed>|string
     */
    private function prepareResponseContent(Response $response): array|string
    {
        $content = (string) $response->getContent();

        if (!json_validate($content)) {
            return $content;
        }

        /** @var array<array-key, mixed> */
        return $this->normalizer->normalize(
            new JsonSource($content),
        );
    }

    private function shouldLogUser(): bool
    {
        if ($this->controllerReflection === null) {
            return false;
        }

        $foundAttribute = $this->controllerReflection->getAttributes(IsGranted::class);

        return $foundAttribute !== [];
    }

    private function getUserId(Request $request): ?string
    {
        try {
            $token = $this->tokenManager->getToken($request);
        } catch (TokenException) {
            return null;
        }

        return (string) $token->getUserId()->value;
    }

    private function setControllerReflection(Request $request): void
    {
        try {
            /** @var array{_controller: class-string} $route */
            $route = $this->router->match($request->getPathInfo());
        } catch (ExceptionInterface) {
            return;
        }

        $controllerClass = $route['_controller'];
        if (!class_exists($controllerClass)) {
            return;
        }

        $this->controllerReflection = new ReflectionClass($controllerClass);
    }
}
