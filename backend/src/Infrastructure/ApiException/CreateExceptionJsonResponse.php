<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Сериализует объект ошибки в JsonResponse
 */
#[AsService]
final readonly class CreateExceptionJsonResponse
{
    public function __construct(private SerializerInterface $serializer) {}

    public function __invoke(ApiException $e): JsonResponse
    {
        $content = $this->serializer->serialize(
            new ApiObjectResponse(
                new ApiErrorResponse(
                    message: $e->getErrorMessage(),
                    errors: $e->getErrors(),
                    code: $e->getApiCode(),
                ),
                ResponseStatus::Error
            ),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $e->getHttpCode(), [], true);
    }
}
