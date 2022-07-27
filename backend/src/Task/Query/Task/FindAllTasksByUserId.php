<?php

declare(strict_types=1);

namespace App\Task\Query\Task;

use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class FindAllTasksByUserId
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @return TaskData[]
     */
    public function __invoke(FindAllTasksByUserIdQuery $query): array
    {
        $sql = <<<'SQL'
                SELECT
                    BIN_TO_UUID(id) as id,
                    task_name_value as taskName,
                    is_completed as isCompleted,
                    created_at as createdAt
                FROM task
                WHERE user_id = :user_id
                ORDER BY is_completed, created_at DESC
            SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('user_id', $query->userId->toBinary());

        $result = $stmt->executeQuery()->fetchAllAssociative();

        $class = TaskData::class;

        /** @var TaskData[] $tasks */
        $tasks = $this->denormalizer->denormalize($result, "{$class}[]", null, ['disable_type_enforcement' => true]);

        return $tasks;
    }
}
