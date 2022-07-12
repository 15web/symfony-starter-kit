<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CompleteTask;
use App\Task\Model\Task;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/complete', methods: ['POST'])]
#[ParamConverter('task', Task::class)]
final class CompleteTaskAction
{
    public function __construct(private readonly CompleteTask $completeTask)
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

        try {
            ($this->completeTask)($task);
        } catch (\DomainException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
