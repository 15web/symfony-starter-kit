<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiErrorResponse;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $apiException = new ApiUnauthorizedException();
        $content = $this->serializer->serialize(
            new ApiErrorResponse($apiException->getErrorMessage(), $apiException->getApiCode()),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $apiException->getHttpCode(), [], true);
    }
}
