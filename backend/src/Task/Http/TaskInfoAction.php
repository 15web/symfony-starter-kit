<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Query\FindById\FindTaskById;
use App\Task\Query\FindById\TaskData;
use App\Task\Query\FindById\TaskNotFoundException;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}', methods: ['GET'])]
final class TaskInfoAction
{
    public function __construct(private readonly FindTaskById $findTaskById)
    {
    }

    public function __invoke(Uuid $id, #[CurrentUser] ?User $user): TaskData
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        try {
            $taskData = ($this->findTaskById)($id, $user->getId());
        } catch (TaskNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return $taskData;
    }
}
