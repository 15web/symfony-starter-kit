<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use Exception;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimit;
use Throwable;

/**
 * Исключение для ошибки 429 (Too Many Requests)
 */
final class ApiRateLimiterException extends Exception implements ApiException, ApiHeaders
{
    private const string MESSAGE = 'Превышено количество запросов. Повторите попытку через %d c';

    /**
     * @var non-negative-int
     */
    private readonly int $retryAfter;

    public function __construct(
        private readonly RateLimit $limit,
        ?Throwable $previous = null,
    ) {
        parent::__construct(previous: $previous);

        /** @var non-negative-int $retryAfter */
        $retryAfter = $this->limit->getRetryAfter()->getTimestamp() - time();
        $this->retryAfter = $retryAfter;
    }

    #[Override]
    public function getErrorMessage(): string
    {
        return 'Превышено количество запросов';
    }

    #[Override]
    public function getHttpCode(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }

    #[Override]
    public function getApiCode(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }

    #[Override]
    public function getErrors(): array
    {
        return [sprintf(self::MESSAGE, $this->retryAfter)];
    }

    #[Override]
    public function getHeaders(): array
    {
        return [
            'X-RateLimit-Remaining' => (string) $this->limit->getRemainingTokens(),
            'X-RateLimit-Retry-After' => (string) $this->retryAfter,
            'X-RateLimit-Limit' => (string) $this->limit->getLimit(),
        ];
    }
}
