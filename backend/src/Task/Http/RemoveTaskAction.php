<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Model\Task;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/remove', methods: ['POST'])]
#[ParamConverter('task', Task::class)]
final class RemoveTaskAction
{
    public function __construct(private readonly RemoveTask $removeTask)
    {
    }

    public function __invoke(Task $task, #[CurrentUser] ?User $user): SuccessResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user->getId()->equals($task->getUserId()) === false) {
            throw new ApiNotFoundException();
        }

        ($this->removeTask)($task);

        return new SuccessResponse();
    }
}
