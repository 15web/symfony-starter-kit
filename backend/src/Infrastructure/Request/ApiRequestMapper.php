<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;
use Throwable;
use Webmozart\Assert\InvalidArgumentException as WebmozartInvalidArgumentException;

/**
 * Десериализатор и валидатор JSON -> DTO.
 *
 * Является декоратором \CuyZ\Valinor\Mapper\TypeTreeMapper
 *  - Добавляет конструктор Uuid из фабричного метода Uuid::fromString()
 *      и обработку его исключений.
 *  - Переопределяет настройки фильтрации исключений,
 *      установленные в конфигурации config/packages/valinor.yaml.
 */
#[AsService]
final readonly class ApiRequestMapper
{
    private TreeMapper $mapper;

    public function __construct(MapperBuilder $builder)
    {
        $this->mapper = $builder
            ->registerConstructor($this->registerUuidConstructor(...))
            ->filterExceptions($this->filterAllowedExceptions(...))
            ->mapper();
    }

    /**
     * Десериализация и валидация JSON -> DTO.
     *
     * @template T of object
     *
     * @param class-string<T> $signature
     *
     * @return T
     *
     * @throws MappingError
     */
    public function map(string $signature, Source $source): mixed
    {
        return $this->mapper->map($signature, $source);
    }

    /**
     * Регистрация собственных конструкторов.
     *
     * @throws ApiRequestMappingException
     */
    private function registerUuidConstructor(string $uuid): Uuid
    {
        try {
            return Uuid::fromString($uuid);
        } catch (InvalidArgumentException $e) {
            throw new ApiRequestMappingException($e);
        }
    }

    /**
     * Фильтрация исключений:
     *  все разрешённые исключения пересобираются в ошибку для отображения,
     *  остальные выбрасываются дальше.
     *
     * @throws Throwable
     */
    private function filterAllowedExceptions(Throwable $exception): ErrorMessage
    {
        $exceptionClass = $exception::class;

        $allowedExceptionsClasses = [
            WebmozartInvalidArgumentException::class,
            ApiRequestMappingException::class,
        ];

        if (\in_array($exceptionClass, $allowedExceptionsClasses, true)) {
            return MessageBuilder::from($exception);
        }

        throw $exception;
    }
}
