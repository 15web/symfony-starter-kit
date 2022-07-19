<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\SuccessResponse;
use App\Task\Command\RemoveTask;
use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/remove', methods: ['POST'])]
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
