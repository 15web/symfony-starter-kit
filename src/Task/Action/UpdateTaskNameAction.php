<?php

declare(strict_types=1);

namespace App\Task\Action;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\UpdateTaskName\UpdateTaskName;
use App\Task\Command\UpdateTaskName\UpdateTaskNameCommand;
use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks/{id}/update-task-name', methods: ['POST'])]
#[ParamConverter('task', Task::class)]
final class UpdateTaskNameAction
{
    public function __construct(private readonly UpdateTaskName $updateTaskName)
    {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command): SuccessResponse
    {
        try {
            ($this->updateTaskName)($task, $command);
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
