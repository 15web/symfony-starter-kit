<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\ApiException\ApiBadRequestException;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;
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
    public function __construct() {}

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

        try {
            $requestObject = (new MapperBuilder())
                ->filterExceptions(function (Throwable $exception) {
                    if ($exception instanceof InvalidArgumentException) {
                        return MessageBuilder::from($exception);
                    }

                    throw $exception;
                })
                ->mapper()
                ->map(
                    signature: $className,
                    source: new JsonSource($request->getContent()),
                )
            ;
        } catch (MappingError $e) {

            $messages = Messages::flattenFromNode(
                node: $e->node()
            );

            $errorMessages = $messages->errors();

            $allMessages = '';
            foreach ($errorMessages as $message) {
                $allMessages .= ' '. $message
                        ->withParameter('source_value', $message->node()->path())
                        ->toString();
            }

            throw new ApiBadRequestException($allMessages, $e);
        } catch (InvalidSource $e) {
            throw new ApiBadRequestException($e->getMessage(), $e);
        }

        return [$requestObject];
    }
}
