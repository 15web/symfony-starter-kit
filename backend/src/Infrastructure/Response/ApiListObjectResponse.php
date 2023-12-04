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
     */
    public function __construct(
        public iterable $data,
        public PaginationResponse $pagination,
        public ResponseStatus $status = ResponseStatus::Success
    ) {}
}
