<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Pagination\PaginationResponse;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Тестирование ответа пагинации
 */
final class PaginationResponseTest extends TestCase
{
    /**
     * @testdox Проверка конструктора, расчет количества страниц
     */
    public function testSuccess(): void
    {
        $paginationResponse = new PaginationResponse(total: 10, perPage: 2, currentPage: 1);

        self::assertSame(5, $paginationResponse->pagesCount);
        self::assertSame(10, $paginationResponse->total);
        self::assertSame(1, $paginationResponse->currentPage);
    }

    /**
     * @testdox Проверка конструктора, нулевое общее количество
     */
    public function testEmpty(): void
    {
        $paginationResponse = new PaginationResponse(total: 0, perPage: 1, currentPage: 1);

        self::assertSame(1, $paginationResponse->pagesCount);
        self::assertSame(1, $paginationResponse->perPage);
        self::assertSame(1, $paginationResponse->currentPage);
    }

    /**
     * @testdox Неверное кол-во данных
     */
    public function testIncorrectTotalDataCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationResponse(total: -10, perPage: 2, currentPage: 1);
    }

    /**
     * @dataProvider incorrectPageAndPerPage
     *
     * @testdox Неверное кол-во на страницу
     */
    public function testIncorrectPerPage(int $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationResponse(total: 10, perPage: $value, currentPage: 1);
    }

    /**
     * @dataProvider incorrectPageAndPerPage
     *
     * @testdox Неверный номер текущей страницы
     */
    public function testIncorrectCurrentPage(int $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationResponse(total: 10, perPage: 1, currentPage: $value);
    }

    public function incorrectPageAndPerPage(): Iterator
    {
        yield 'отрицательное значение' => [-1];

        yield 'нулевое значение' => [0];
    }
}
