<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Response\Pagination\PaginationResponse;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Тестирование ответа пагинации')]
final class PaginationResponseTest extends TestCase
{
    #[TestDox('Проверка конструктора')]
    public function testSuccess(): void
    {
        $paginationResponse = new PaginationResponse(total: 10);

        self::assertSame(10, $paginationResponse->total);
    }

    #[TestDox('Общее кол-во не может быть отрицательным')]
    public function testIncorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationResponse(-1);
    }
}
