<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use App\User\Domain\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks', methods: ['GET'])]
#[AsController]
final class TaskListAction
{
    public function __construct(private readonly FindAllTasksByUserId $findAllTasksByUserId)
    {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(UserId $userId): array
    {
        return ($this->findAllTasksByUserId)(new FindAllTasksByUserIdQuery($userId->value));
    }
}
