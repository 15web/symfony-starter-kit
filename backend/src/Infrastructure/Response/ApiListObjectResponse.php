<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use App\Infrastructure\Response\Pagination\PaginationResponse;

/**
 * Ответ со списками и пагинацией
 */
final readonly class ApiListObjectResponse implements ResponseInterface
{
    /**
     * @param iterable<object> $data
     * @param object|null $meta Дополнительная мета-информация в ответе (фильтры, ссылки и т.п.)
     */
    public function __construct(
        public iterable $data,
        public PaginationResponse $pagination,
        public ?object $meta = null,
        public ResponseStatus $status = ResponseStatus::Success
    ) {}
}
