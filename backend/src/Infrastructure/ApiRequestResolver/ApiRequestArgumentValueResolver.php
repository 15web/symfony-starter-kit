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
 *
 * @template TApiRequest of object
 */
#[AsService]
final readonly class ApiRequestArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @return iterable<TApiRequest>
     *
     * @throws ApiBadRequestException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var class-string<TApiRequest>|null $className */
        $className = $argument->getType();
        if ($className === null) {
            return [];
        }

        $attributes = $argument->getAttributesOfType(ApiRequest::class, ArgumentMetadata::IS_INSTANCEOF);
        if ($attributes === []) {
            return [];
        }

        if ($request->getContentTypeFormat() !== JsonEncoder::FORMAT) {
            throw new ApiBadRequestException('Укажите json');
        }

        if ($request->getContent() === '') {
            throw new ApiBadRequestException('Укажите контент запроса');
        }

        try {
            $requestObject = $this->serializer->deserialize(
                $request->getContent(),
                $className,
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
