<?php

declare(strict_types=1);

namespace App\Task\Http\CreateTask;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\CreateTask\CreateTaskCommand;
use App\Task\Domain\TaskId;
use App\User\Domain\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/create', methods: ['POST'])]
#[AsController]
final class CreateTaskAction
{
    public function __construct(private readonly CreateTask $createTask)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, UserId $userId): TaskData
    {
        try {
            $taskId = new TaskId();
            ($this->createTask)($createTaskCommand, $taskId, $userId);
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new TaskData($taskId->value);
    }
}
