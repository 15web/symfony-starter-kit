<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Наименование задачи')]
final class TaskNameTest extends TestCase
{
    #[TestDox('Наименования идентичны')]
    public function testEquals(): void
    {
        $taskName1 = new TaskName('new task');
        $taskName2 = new TaskName('new task');

        self::assertTrue($taskName1->equalTo($taskName2));
    }

    #[TestDox('Нельзя создать пустое наименование задачи')]
    public function testEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         */
        new TaskName('');
    }
}
