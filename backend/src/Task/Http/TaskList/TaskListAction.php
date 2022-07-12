<?php

declare(strict_types=1);

namespace App\Task\Http\TaskList;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Model\Tasks;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks', methods: ['GET'])]
final class TaskListAction
{
    public function __construct(private readonly Tasks $tasks)
    {
    }

    /**
     * @return \Generator<TaskData>
     */
    public function __invoke(#[CurrentUser] ?User $user): \Generator
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        $tasks = $this->tasks->findAllByUserId($user->getId());
        foreach ($tasks as $task) {
            yield new TaskData(
                $task->getId(),
                $task->getTaskName()->getValue(),
                $task->isCompleted(),
            );
        }
    }
}
