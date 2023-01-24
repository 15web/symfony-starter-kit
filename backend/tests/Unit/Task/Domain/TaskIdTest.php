<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @testdox Тестируемый класс: (App\Tests\Unit\Task\Domain\TaskId)
 */
final class TaskIdTest extends TestCase
{
    /**
     * @testdox Проверка метода testEquals прошла успешно
     */
    public function testEquals(): void
    {
        $taskId1 = new TaskId();
        $taskId2 = new TaskId();

        self::assertFalse($taskId1->equalTo($taskId2));
    }
}
