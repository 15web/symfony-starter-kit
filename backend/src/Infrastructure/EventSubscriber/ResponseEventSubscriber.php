<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Infrastructure\AsService;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Сериализует ответ ручки в JsonResponse
 */
#[AsService]
final readonly class ResponseEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @return array<string, array{0: string, 1: int}|list<array{0: string, 1?: int}>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setResponse', 0],
            ],
        ];
    }

    public function setResponse(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();

        if (!\is_object($controllerResult) && !is_iterable($controllerResult)) {
            throw new RuntimeException('Не поддерживаемый ответ');
        }

        if ($controllerResult instanceof Response) {
            return;
        }

        $controllerResult = $this->serializer->serialize(
            data: $controllerResult,
            format: JsonEncoder::FORMAT,
        );
        $response = new JsonResponse(
            data: $controllerResult,
            status: Response::HTTP_OK,
            headers: [],
            json: true,
        );

        $event->setResponse($response);
    }
}