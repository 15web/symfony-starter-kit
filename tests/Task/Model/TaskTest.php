<?php

namespace App\Tests\Task\Model;

use App\Task\Model\Task;
use App\Task\Model\TaskName;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testCorrectCreation(): void
    {
        $task = new Task(new TaskName($expectedName = 'new task'));

        self::assertSame($expectedName, $task->getTaskName()->getValue());
        self::assertNotNull($task->getCreatedAt());
        self::assertNull($task->getUpdatedAt());
    }

    public function testCorrectChangeName(): void
    {
        $task = new Task(new TaskName('new task'));
        $task->changeTaskName(new TaskName($expectedNewName = 'changed task'));

        self::assertSame($expectedNewName, $task->getTaskName()->getValue());
        self::assertNotNull($task->getUpdatedAt());
    }
}
