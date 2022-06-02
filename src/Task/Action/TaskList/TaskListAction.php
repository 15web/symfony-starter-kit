<?php

declare(strict_types=1);

namespace App\Task\Action\TaskList;

use App\Task\Model\Tasks;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks', methods: ['GET'])]
final class TaskListAction
{
    public function __construct(private readonly Tasks $tasks)
    {
    }

    /**
     * @return \Generator<TaskData>
     */
    public function __invoke(): \Generator
    {
        $tasks = $this->tasks->findAll();
        foreach ($tasks as $task) {
            yield new TaskData(
                $task->getId(),
                $task->getTaskName()->getValue(),
                $task->isCompleted(),
            );
        }
    }
}
