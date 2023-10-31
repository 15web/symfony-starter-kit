<?php

declare(strict_types=1);

namespace App\Mailer\Notification\UncompletedTasks;

use App\Infrastructure\ValueObject\Email;

/**
 * Содержит список невыполненных задач
 */
final readonly class UncompletedTasksMessage
{
    /**
     * @param TaskData[] $tasks
     */
    public function __construct(public Email $email, public array $tasks) {}
}
