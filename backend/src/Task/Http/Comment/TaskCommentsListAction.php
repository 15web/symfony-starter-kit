<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Task\Query\Comment\FindAll\CommentData;
use App\Task\Query\Comment\FindAll\FindAllCommentQuery;
use App\Task\Query\Comment\FindAll\FindAllCommentsByTaskIdAndUserId;
use App\User\Domain\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/comments', methods: ['GET'])]
#[AsController]
final class TaskCommentsListAction
{
    public function __construct(private readonly FindAllCommentsByTaskIdAndUserId $findAllComments)
    {
    }

    /**
     * @return CommentData[]
     */
    public function __invoke(Uuid $id, UserId $userId): array
    {
        return ($this->findAllComments)(new FindAllCommentQuery($id, $userId->value));
    }
}
