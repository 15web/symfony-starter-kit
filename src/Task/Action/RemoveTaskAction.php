<?php

declare(strict_types=1);

namespace App\Task\Action;

use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks/{id}/remove', methods: ['POST'])]
#[ParamConverter('task', Task::class)]
final class RemoveTaskAction
{
    public function __construct(private readonly RemoveTask $removeTask)
    {
    }

    public function __invoke(Task $task): SuccessResponse
    {
        ($this->removeTask)($task);

        return new SuccessResponse();
    }
}
