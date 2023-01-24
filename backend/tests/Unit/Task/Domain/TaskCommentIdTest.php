<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskCommentId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @testdox Тестируемый класс: (App\Tests\Unit\Task\Domain\TaskCommentId)
 */
final class TaskCommentIdTest extends TestCase
{
    /**
     * @testdox Проверка метода testEquals прошла успешно
     */
    public function testEquals(): void
    {
        $taskCommentId1 = new TaskCommentId();
        $taskCommentId2 = new TaskCommentId();

        self::assertFalse($taskCommentId1->equalTo($taskCommentId2));
    }
}
