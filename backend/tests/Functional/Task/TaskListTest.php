<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;

final class TaskListTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        Task::create('Тестовая задача 1');
        Task::create('Тестовая задача 2');

        $tasks = Task::list();

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertNotNull($task['id']);
            self::assertFalse($task['isCompleted']);
            self::assertNotNull($task['taskName']);
        }
    }
}
