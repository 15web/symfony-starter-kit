<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Model;

use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use App\Task\Domain\TaskName;
use App\User\Domain\User;
use App\User\Domain\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class TaskTest extends TestCase
{
    public function testCorrectCreation(): void
    {
        $user = new User(new UserEmail('test@example.com'));

        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());

        self::assertTrue($task->isBelongToUser($user->getId()));
    }
    public function testAlreadyCompletedTask(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        $this->expectException(TaskAlreadyIsDoneException::class);
        $task->markAsDone();
    }
}
