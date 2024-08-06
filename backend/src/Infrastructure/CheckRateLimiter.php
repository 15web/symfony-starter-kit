<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\ApiException\ApiRateLimiterException;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Проверка рейт-лимитера
 */
#[AsService]
final readonly class CheckRateLimiter
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    /**
     * @param non-empty-string|null $key
     *
     * @throws ApiRateLimiterException
     */
    public function __invoke(
        RateLimiterFactory $rateLimiter,
        ?string $key,
    ): LimiterInterface {
        $limiter = $rateLimiter->create($key);
        $limit = $limiter->consume();

        if ($limit->isAccepted() === false) {
            $this->logger->info(
                message: 'Превышено допустимое количество запросов',
                context: ['key' => $key]
            );

            throw new ApiRateLimiterException($limit);
        }

        return $limiter;
    }
}
