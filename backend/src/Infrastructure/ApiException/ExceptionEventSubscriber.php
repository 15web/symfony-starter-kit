<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class ExceptionEventSubscriber implements EventSubscriberInterface
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
            KernelEvents::EXCEPTION => [
                ['setResponse', -1],
            ],
        ];
    }

    public function setResponse(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!\in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return;
        }

        $e = $event->getThrowable();

        if (($e instanceof ApiException) === false) {
            return;
        }

        $content = $this->serializer->serialize(
            new ApiErrorResponse($e->getErrorMessage(), $e->getApiCode()),
            JsonEncoder::FORMAT
        );

        $response = new JsonResponse($content, $e->getHttpCode(), [], true);
        $event->setResponse($response);
        $event->allowCustomResponseCode();
    }
}
