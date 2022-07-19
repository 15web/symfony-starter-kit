<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiRequestResolver;

use App\Infrastructure\ApiException\ApiBadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiRequestArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getType() === null) {
            return false;
        }

        return is_subclass_of($argument->getType(), ApiRequest::class);
    }

    /**
     * @throws ApiBadRequestException
     *
     * @return iterable<ApiRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() === null) {
            throw new ApiBadRequestException('Укажите объект запроса');
        }

        if ($request->getContentType() !== JsonEncoder::FORMAT) {
            throw new ApiBadRequestException('Укажите json');
        }

        try {
            /** @var ApiRequest $requestObject */
            $requestObject = $this->serializer->deserialize(
                $request->getContent(),
                $argument->getType(),
                JsonEncoder::FORMAT
            );
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        } catch (\Exception) {
            throw new ApiBadRequestException('Неверный формат запроса');
        }

        yield $requestObject;
    }
}
