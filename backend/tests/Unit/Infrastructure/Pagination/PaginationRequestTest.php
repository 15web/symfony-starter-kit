<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Pagination\PaginationRequest;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Тестирование запроса пагинации
 */
final class PaginationRequestTest extends TestCase
{
    /**
     * @testdox Проверка настроек по умолчанию
     */
    public function testSuccessDefault(): void
    {
        $paginationRequest = new PaginationRequest();

        self::assertSame(1, $paginationRequest->page);
        self::assertSame(10, $paginationRequest->perPage);
        self::assertSame(0, $paginationRequest->getOffset());
    }

    /**
     * @testdox Проверка конструктора, расчет оффсета
     */
    public function testSuccess(): void
    {
        $paginationRequest = new PaginationRequest(3, 15);

        self::assertSame(3, $paginationRequest->page);
        self::assertSame(15, $paginationRequest->perPage);
        self::assertSame(30, $paginationRequest->getOffset());
    }

    /**
     * @testdox Неверный номер страницы
     */
    public function testIncorrectPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PaginationRequest(0);
    }

    /**
     * @testdox Неверное кол-во на страницу
     */
    public function testIncorrectPerPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PaginationRequest(perPage: -1);
    }
}
