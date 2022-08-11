<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\AsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsService]
final class ResponseEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SerializerInterface $serializer)
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
        if ($controllerResult instanceof Response) {
            return;
        }

        $controllerResult = $this->serializer->serialize($controllerResult, JsonEncoder::FORMAT);
        $response = new JsonResponse($controllerResult, Response::HTTP_OK, [], true);

        $event->setResponse($response);
    }
}
