<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\Comment\Add\AddCommentOnTask;
use App\Task\Command\Comment\Add\AddCommentOnTaskCommand;
use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskCommentId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}/add-comment', methods: ['POST'])]
#[AsController]
final class AddCommentOnTaskAction
{
    public function __construct(private readonly AddCommentOnTask $addCommentOnTask)
    {
    }

    public function __invoke(Task $task, AddCommentOnTaskCommand $addCommentOnTaskCommand): SuccessResponse
    {
        try {
            $commentId = new TaskCommentId();
            ($this->addCommentOnTask)($addCommentOnTaskCommand, $task, $commentId);
        } catch (AddCommentToCompletedTaskException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        return new SuccessResponse();
    }
}
