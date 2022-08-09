<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Query\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\Task\Query\FindAllByUserId\TaskData;
use App\User\Domain\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks', methods: ['GET'])]
final class TaskListAction
{
    public function __construct(private readonly FindAllTasksByUserId $findAllTasksByUserId)
    {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(#[CurrentUser] ?User $user): array
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        return ($this->findAllTasksByUserId)(new FindAllTasksByUserIdQuery($user->getId()));
    }
}