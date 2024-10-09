<?php

declare(strict_types=1);

namespace App\Task\Scheduler;

use App\Infrastructure\AsService;
use App\Mailer\Notification\UncompletedTasks\TaskData;
use App\Mailer\Notification\UncompletedTasks\UncompletedTasksMessage;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserId;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserIdQuery;
use App\Task\Query\Task\FindUncompletedTasksByUserId\TaskData as QueryTaskData;
use App\User\User\Query\FindAllUsers;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

/**
 * Шедулер отправки пользователю списка невыполненных задач
 */
#[AsCronTask('15 21 */1 * *')]
#[AsService]
final readonly class SendUncompletedTaskToUserScheduler
{
    public function __construct(
        private FindUncompletedTasksByUserId $findUncompletedTasksByUserId,
        private FindAllUsers $findAllUsers,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {}

    public function __invoke(): void
    {
        $users = ($this->findAllUsers)();
        $emailSent = 0;

        foreach ($users as $user) {
            $uncompletedTasks = ($this->findUncompletedTasksByUserId)(
                new FindUncompletedTasksByUserIdQuery($user->id)
            );

            if ($uncompletedTasks === []) {
                continue;
            }

            $this->messageBus->dispatch(
                new UncompletedTasksMessage(
                    email: $user->email,
                    tasks: array_map(
                        static fn (QueryTaskData $task): TaskData => new TaskData(taskName: $task->taskName, createdAt: $task->createdAt),
                        $uncompletedTasks
                    )
                )
            );

            ++$emailSent;
        }

        $this->logger->info(
            \sprintf(
                'Отправлено %d писем о невыполненных задачах',
                $emailSent
            )
        );
    }
}
