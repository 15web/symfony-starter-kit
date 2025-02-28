<?php

declare(strict_types=1);

namespace App\Logger\Http;

use App\Infrastructure\AsService;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Override;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Запись идентификатора запроса во все записи журнала
 */
#[AsService]
#[AsMonologProcessor]
final readonly class RequestIdLoggerProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    #[Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null && $request->headers->has(RequestIdListener::TRACE_ID_HEADER)) {
            $record->extra['traceId'] = $request->headers->get(RequestIdListener::TRACE_ID_HEADER);
        }

        return $record;
    }
}
