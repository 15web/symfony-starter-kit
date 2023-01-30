<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Сериализует объект ошибки в JsonResponse
 */
#[AsService]
final class CreateExceptionJsonResponse
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function __invoke(ApiException $e): JsonResponse
    {
        $content = $this->serializer->serialize(
            new ApiErrorResponse($e->getErrorMessage(), $e->getApiCode()),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $e->getHttpCode(), [], true);
    }
}
