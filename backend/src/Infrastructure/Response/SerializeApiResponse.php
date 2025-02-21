<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\JsonNormalizer;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Uid\Uuid;

/**
 * Преобразовывает объект ответа контроллеров в JsonResponse
 */
#[AsEventListener]
final readonly class SerializeApiResponse
{
    private JsonNormalizer $normalizer;

    public function __construct(MapperBuilder $builder)
    {
        $this->normalizer = $builder
            ->registerTransformer(static fn (Uuid $uuid): string => $uuid->toString())
            ->registerTransformer(static fn (DateTimeImmutable $date): string => $date->format(DateTimeInterface::ATOM))
            ->normalizer(Format::json())
            ->withOptions(JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function __invoke(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();

        if (!$controllerResult instanceof ApiResponse) {
            throw new RuntimeException('Не поддерживаемый ответ');
        }

        $serializedResult = $this->normalizer->normalize($controllerResult);

        $response = new JsonResponse(
            data: $serializedResult,
            status: Response::HTTP_OK,
            headers: [],
            json: true,
        );

        $event->setResponse($response);
    }
}
