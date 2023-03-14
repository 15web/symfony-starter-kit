<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiRequestResolver;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\AsService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * Преобразует Request в нужный для ручки объект запроса
 */
#[AsService]
final readonly class ApiRequestArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @return iterable<ApiRequest>
     *
     * @throws ApiBadRequestException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() === null) {
            return [];
        }

        if (!is_subclass_of($argument->getType(), ApiRequest::class)) {
            return [];
        }

        if ($request->getContentTypeFormat() !== JsonEncoder::FORMAT) {
            throw new ApiBadRequestException('Укажите json');
        }

        try {
            /** @var ApiRequest $requestObject */
            $requestObject = $this->serializer->deserialize(
                $request->getContent(),
                $argument->getType(),
                JsonEncoder::FORMAT
            );
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage(), $e);
        } catch (Throwable $e) {
            throw new ApiBadRequestException('Неверный формат запроса', $e);
        }

        return [$requestObject];
    }
}
