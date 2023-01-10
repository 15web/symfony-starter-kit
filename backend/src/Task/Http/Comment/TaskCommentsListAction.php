<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Task\Query\Comment\FindAll\CommentData;
use App\Task\Query\Comment\FindAll\FindAllCommentQuery;
use App\Task\Query\Comment\FindAll\FindAllCommentsByTaskIdAndUserId;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{taskId}/comments', methods: ['GET'])]
#[AsController]
final class TaskCommentsListAction
{
    public function __construct(private readonly FindAllCommentsByTaskIdAndUserId $findAllComments)
    {
    }

    /**
     * @return CommentData[]
     */
    public function __invoke(Uuid $taskId, UserId $userId): array
    {
        return ($this->findAllComments)(new FindAllCommentQuery($taskId, $userId->value));
    }
}
