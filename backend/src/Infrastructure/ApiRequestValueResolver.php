<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\BuildMappingErrorMessages;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\MapperBuilder;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Throwable;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Преобразует Request в объект запроса
 *
 * @template TApiRequest of object
 */
#[AsService]
final readonly class ApiRequestValueResolver implements ValueResolverInterface
{
    public function __construct(private BuildMappingErrorMessages $buildMappingErrorMessages) {}

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

        try {
            $requestObject = (new MapperBuilder())
                ->filterExceptions(static function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof InvalidArgumentException) {
                        return MessageBuilder::from($exception);
                    }

                    throw $exception;
                })
                ->mapper()
                ->map(
                    signature: $className,
                    source: new JsonSource($request->getContent()),
                );
        } catch (MappingError $e) {
            $errorMessages = ($this->buildMappingErrorMessages)($e);

            throw new ApiBadRequestException($errorMessages, $e);
        } catch (InvalidSource $e) {
            throw new ApiBadRequestException(['Невалидный json'], $e);
        }

        return [$requestObject];
    }
}
