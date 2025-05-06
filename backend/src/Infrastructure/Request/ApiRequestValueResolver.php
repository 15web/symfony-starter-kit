<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\ApiException\ApiBadRequestException;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Source;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Преобразует Request в объект запроса, валидирует
 *
 * @template TApiRequest of object
 */
final readonly class ApiRequestValueResolver implements ValueResolverInterface
{
    public function __construct(
        private BuildValidationError $buildValidationError,
        private ApiRequestMapper $mapper,
    ) {}

    /**
     * @return iterable<TApiRequest>
     *
     * @throws ApiBadRequestException
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var class-string<TApiRequest>|null $className */
        $className = $argument->getType();
        if ($className === null) {
            return [];
        }

        $content = $request->getContent();
        if ($content === '') {
            $content = '{}';
        }

        try {
            $requestObject = $this->mapper->map(
                signature: $className,
                source: Source::json($content),
            );
        } catch (MappingError $error) {
            $errorMessages = ($this->buildValidationError)($error);

            throw new ApiBadRequestException($errorMessages, $error);
        } catch (InvalidSource $error) {
            throw new ApiBadRequestException(['Невалидный json'], $error);
        }

        return [$requestObject];
    }
}
