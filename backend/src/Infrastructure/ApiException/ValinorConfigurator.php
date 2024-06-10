<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\ValinorBundle\Configurator\MapperBuilderConfigurator;
use Override;
use Symfony\Component\Uid\Uuid;
use Throwable;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Конфигурация Valinor
 * - настройка кэширования
 * - обработка кастомных исключений
 *
 * @see https://github.com/CuyZ/Valinor-Bundle/#customizing-mapper-builder
 */
#[AsService]
final readonly class ValinorConfigurator implements MapperBuilderConfigurator
{
    #[Override]
    public function configure(MapperBuilder $builder): MapperBuilder
    {
        /* todo разобраться с ошибкой psalm и phpstan, возможно ишью в валиноре создать
         CuyZ\Valinor\MapperBuilder::registerConstructor expects class-string|pure-callable,
         but impure-Closure(string):Symfony\Component\Uid\Uuid provided (see https://psalm.dev/004)
         Parameter #1 ...$constructors of method CuyZ\Valinor\MapperBuilder::registerConstructor() expects (pure-callable(): mixed)|class-string, Closure(string):
         Symfony\Component\Uid\Uuid given.
        */
        return $builder
            // @phpstan-ignore-next-line
            ->registerConstructor(Uuid::fromString(...))
            ->filterExceptions(static function (Throwable $exception): ErrorMessage {
                if ($exception instanceof InvalidArgumentException) {
                    return MessageBuilder::from($exception);
                }

                throw $exception;
            });
    }
}
