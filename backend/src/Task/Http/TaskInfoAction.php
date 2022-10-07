<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Security\UserProvider\SecurityUser;
use App\Task\Query\Task\FindById\FindTaskById;
use App\Task\Query\Task\FindById\FindTaskByIdQuery;
use App\Task\Query\Task\FindById\TaskData;
use App\Task\Query\Task\FindById\TaskNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}', methods: ['GET'])]
#[AsController]
final class TaskInfoAction
{
    public function __construct(private readonly FindTaskById $findTaskById)
    {
    }

    public function __invoke(Uuid $id, SecurityUser $securityUser): TaskData
    {
        try {
            $taskData = ($this->findTaskById)(new FindTaskByIdQuery($id, $securityUser->getId()));
        } catch (TaskNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return $taskData;
    }
}
