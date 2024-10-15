<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use App\Infrastructure\ApiException\ApiErrorResponse;
use App\Infrastructure\ApiException\ApiException;
use App\Infrastructure\ApiException\ApiHeaders;
use App\Infrastructure\ApiException\ApiSystemException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Формирование ответа при ошибках
 */
#[AsEventListener(priority: -1)]
final readonly class SerializeExceptionResponse
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $exception = new ApiSystemException(
                errors: [\sprintf('Http ошибка: %s', $exception->getMessage())],
                status: $exception->getStatusCode(),
                previous: $exception,
            );
        }

        if (!$exception instanceof ApiException) {
            $exception = new ApiSystemException(
                errors: [\sprintf('Ошибка приложения, код ошибки %s', $exception->getCode())],
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $exception,
            );
        }

        $event->setResponse($this->createJson($exception));
        $event->allowCustomResponseCode();
    }

    private function createJson(ApiException $exception): JsonResponse
    {
        $content = $this->serializer->serialize(
            data: new ApiObjectResponse(
                data: new ApiErrorResponse(
                    message: $exception->getErrorMessage(),
                    errors: $exception->getErrors(),
                    code: $exception->getApiCode(),
                ),
                status: ResponseStatus::Error,
            ),
            format: JsonEncoder::FORMAT,
        );

        $headers = [];
        if ($exception instanceof ApiHeaders) {
            $headers = $exception->getHeaders();
        }

        return new JsonResponse(
            data: $content,
            status: $exception->getHttpCode(),
            headers: $headers,
            json: true,
        );
    }
}
