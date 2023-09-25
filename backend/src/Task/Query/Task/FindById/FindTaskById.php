<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindById;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Хендлер нахождения задачи по айди
 */
#[AsService]
final readonly class FindTaskById
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function __invoke(FindTaskByIdQuery $query): TaskData
    {
        $dql = <<<'DQL'
                SELECT
                NEW App\Task\Query\Task\FindById\TaskData(t.id, t.taskName.value, t.isCompleted, t.createdAt, t.completedAt, t.updatedAt)
                FROM App\Task\Domain\Task AS t
                WHERE t.id = :id and t.userId = :userId
            DQL;

        $dqlQuery = $this->entityManager->createQuery($dql);
        $dqlQuery->setParameter('id', $query->taskId->toBinary());
        $dqlQuery->setParameter('userId', $query->userId->toBinary());

        /** @var ?TaskData $result */
        $result = $dqlQuery->getOneOrNullResult();

        if ($result === null) {
            throw new TaskNotFoundException();
        }

        return $result;
    }
}
