<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CompleteTask;
use App\Task\Model\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/complete', methods: ['POST'])]
final class CompleteTaskAction
{
    public function __construct(private readonly CompleteTask $completeTask)
    {
    }

    public function __invoke(Task $task): SuccessResponse
    {
        try {
            ($this->completeTask)($task);
        } catch (\DomainException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
