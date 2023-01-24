<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskCommentBody;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @testdox Тестируемый класс: (App\Tests\Unit\Task\Domain\TaskCommentBody)
 */
final class TaskCommentBodyTest extends TestCase
{
    /**
     * @testdox Проверка метода testEquals прошла успешно
     */
    public function testEquals(): void
    {
        $body1 = new TaskCommentBody('Комментарий');
        $body2 = new TaskCommentBody('Комментарий');

        self::assertTrue($body1->equalTo($body2));
    }

    /**
     * @testdox Проверка метода testEmptyValue прошла успешно
     */
    public function testEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TaskCommentBody('');
    }
}
