<?php

declare(strict_types=1);

namespace App\Task\Query\Comment\FindAll;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения комментариев задачи по пользователю
 */
#[AsService]
final class FindAllCommentsByTaskIdAndUserId
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return CommentData[]
     */
    public function __invoke(FindAllCommentQuery $findAllQuery): array
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\Task\Query\Comment\FindAll\CommentData(c.id, c.body.value, c.createdAt, c.updatedAt)
                FROM App\Task\Domain\TaskComment AS c
                JOIN c.task t
                WHERE t.id = :taskId and t.userId = :userId
                ORDER BY c.createdAt DESC
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('taskId', $findAllQuery->taskId->toBinary());
        $dqlQuery->setParameter('userId', $findAllQuery->userId->toBinary());

        return $dqlQuery->getResult();
    }
}
