<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Request\Pagination\PaginationRequest;
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
        $paginationRequest = new PaginationRequest(
            offset: 3,
            limit: 15,
        );

        self::assertSame(3, $paginationRequest->offset);
        self::assertSame(15, $paginationRequest->limit);
    }
}
