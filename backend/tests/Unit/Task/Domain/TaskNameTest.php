<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskName;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Наименование задачи
 */
final class TaskNameTest extends TestCase
{
    /**
     * @testdox Наименования идентичны
     */
    public function testEquals(): void
    {
        $taskName1 = new TaskName('new task');
        $taskName2 = new TaskName('new task');

        self::assertTrue($taskName1->equalTo($taskName2));
    }

    /**
     * @testdox Нельзя создать пустое наименование задачи
     */
    public function testEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TaskName('');
    }
}
