<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\CreateTask\CreateTaskCommand;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/create', methods: ['POST'])]
final class CreateTaskAction
{
    public function __construct(private readonly CreateTask $createTask)
    {
    }

    public function __invoke(CreateTaskCommand $createTaskCommand, #[CurrentUser] ?User $user): SuccessResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        try {
            ($this->createTask)($createTaskCommand, $user->getId());
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
