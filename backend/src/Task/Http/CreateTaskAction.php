<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\CreateTask\CreateTaskCommand;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks/create', methods: ['POST'])]
final class CreateTaskAction
{
    public function __construct(private readonly CreateTask $createTask)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand): SuccessResponse
    {
        try {
            ($this->createTask)($createTaskCommand);
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
