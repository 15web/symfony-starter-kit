<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Pagination\PaginationRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Тестирование запроса пагинации')]
final class PaginationRequestTest extends TestCase
{
    #[TestDox('Проверка настроек по умолчанию')]
    public function testSuccessDefault(): void
    {
        $paginationRequest = new PaginationRequest();

        self::assertSame(0, $paginationRequest->offset);
        self::assertSame(10, $paginationRequest->limit);
    }

    #[TestDox('Проверка аргументов')]
    public function testSuccess(): void
    {
        $paginationRequest = new PaginationRequest(3, 15);

        self::assertSame(3, $paginationRequest->offset);
        self::assertSame(15, $paginationRequest->limit);
    }

    #[TestDox('Неверный лимит')]
    public function testIncorrectLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationRequest(0, -1);
    }

    #[TestDox('Неверный оффсет')]
    public function testIncorrectPerPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaginationRequest(-1, 10);
    }
}
