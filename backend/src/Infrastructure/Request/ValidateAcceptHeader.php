<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\ApiException\ApiBadRequestException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Проверка заголовка Accept, принимает только application/json
 */
#[AsEventListener]
final readonly class ValidateAcceptHeader
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $acceptableContentTypes = $request->getAcceptableContentTypes();

        if (\in_array('application/json', $acceptableContentTypes, true)) {
            return;
        }

        if (
            $acceptableContentTypes === []
            || \in_array('*/*', $acceptableContentTypes, true)
            || \in_array('application/*', $acceptableContentTypes, true)
        ) {
            $request->headers->set('Accept', 'application/json');

            return;
        }

        throw new ApiBadRequestException(['Укажите заголовок Accept: application/json']);
    }
}
