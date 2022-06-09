<?php

declare(strict_types=1);

namespace App\Tests\Task\Model;

use App\Task\Model\Task;
use App\Task\Model\TaskName;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testCorrectCreation(): void
    {
        $task = new Task(new TaskName($expectedName = 'new task'));

        self::assertSame($expectedName, $task->getTaskName()->getValue());
        self::assertFalse($task->isCompleted());
        self::assertNotNull($task->getCreatedAt());
        self::assertNull($task->getUpdatedAt());
        self::assertNull($task->getCompletedAt());
    }

    public function testCorrectChangeName(): void
    {
        $task = new Task(new TaskName('new task'));
        $task->changeTaskName(new TaskName($expectedNewName = 'changed task'));

        self::assertSame($expectedNewName, $task->getTaskName()->getValue());
        self::assertNotNull($task->getUpdatedAt());
    }

    public function testMarkAsDone(): void
    {
        $task = new Task(new TaskName('new task'));
        $task->markAsDone();

        self::assertTrue($task->isCompleted());
        self::assertNotNull($task->getUpdatedAt());
        self::assertNotNull($task->getCompletedAt());
    }

    public function testAlreadyCompletedTask(): void
    {
        $task = new Task(new TaskName('new task'));
        $task->markAsDone();

        $this->expectException(\DomainException::class);
        $task->markAsDone();
    }
}
