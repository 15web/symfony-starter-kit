<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Pagination\PaginationResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Тестирование ответа пагинации
 */
final class PaginationResponseTest extends TestCase
{
    /**
     * @testdox Проверка конструктора
     */
    public function testSuccess(): void
    {
        $paginationResponse = new PaginationResponse(total: 10);

        self::assertSame(10, $paginationResponse->total);
    }

    /**
     * @testdox Общее кол-во не может быть отрицательным
     */
    public function testIncorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationResponse(-1);
    }
}
