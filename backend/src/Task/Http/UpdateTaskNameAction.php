<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\UpdateTaskName\UpdateTaskName;
use App\Task\Command\UpdateTaskName\UpdateTaskNameCommand;
use App\Task\Model\Task;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/update-task-name', methods: ['POST'])]
#[ParamConverter('task', Task::class)]
final class UpdateTaskNameAction
{
    public function __construct(private readonly UpdateTaskName $updateTaskName)
    {
    }

    public function __invoke(Task $task, UpdateTaskNameCommand $command, #[CurrentUser] ?User $user): SuccessResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user->getId()->equals($task->getUserId()) === false) {
            throw new ApiNotFoundException();
        }

        try {
            ($this->updateTaskName)($task, $command);
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
