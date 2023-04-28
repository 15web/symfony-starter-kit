<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\TaskId;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('ID задачи')]
final class TaskIdTest extends TestCase
{
    #[TestDox('Идентификаторы идентичны')]
    public function testEquals(): void
    {
        $taskId1 = new TaskId();
        $taskId2 = new TaskId();

        self::assertFalse($taskId1->equalTo($taskId2));
    }
}
