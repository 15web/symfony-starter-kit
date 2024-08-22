<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use RuntimeException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Преобразовывает объект ответа контроллеров в JsonResponse
 */
#[AsEventListener]
final readonly class SerializeApiResponse
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    public function __invoke(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();

        if (!$controllerResult instanceof ApiResponse) {
            throw new RuntimeException('Не поддерживаемый ответ');
        }

        $serializedResult = $this->serializer->serialize(
            data: $controllerResult,
            format: JsonEncoder::FORMAT,
        );

        $response = new JsonResponse(
            data: $serializedResult,
            status: Response::HTTP_OK,
            headers: [],
            json: true,
        );

        $event->setResponse($response);
    }
}
