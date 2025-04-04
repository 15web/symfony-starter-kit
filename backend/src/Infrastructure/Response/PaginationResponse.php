<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use Webmozart\Assert\Assert;

/**
 * Ответ пагинации. Содержит общее кол-во записей
 */
final readonly class PaginationResponse
{
    public function __construct(public int $total)
    {
        Assert::natural($total, 'total: число не может быть отрицательным, указано %s');
    }
}
