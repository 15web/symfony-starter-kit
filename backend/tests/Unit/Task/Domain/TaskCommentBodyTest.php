<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskCommentBody;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Комментарий задачи')]
final class TaskCommentBodyTest extends TestCase
{
    #[TestDox('Комментарии идентичны')]
    public function testEquals(): void
    {
        $body1 = new TaskCommentBody('Комментарий');
        $body2 = new TaskCommentBody('Комментарий');

        self::assertTrue($body1->equalTo($body2));
    }

    #[TestDox('Нельзя создать пустой комментарий')]
    public function testEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TaskCommentBody('');
    }
}
