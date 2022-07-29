<?php

declare(strict_types=1);

namespace App\Task\Query\FindById;

use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Uid\Uuid;

final class FindTaskById
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function __invoke(Uuid $id, Uuid $userId): TaskData
    {
        $sql = <<<'SQL'
                SELECT
                    BIN_TO_UUID(id) as id,
                    task_name_value as taskName,
                    is_completed as isCompleted,
                    created_at as createdAt,
                    completed_at as completedAt,
                    updated_at as updatedAt
                FROM task
                WHERE id = :id and user_id = :user_id
            SQL;

        $result = $this->connection
            ->executeQuery($sql, ['id' => $id->toBinary(), 'user_id' => $userId->toBinary()])
            ->fetchAssociative();

        if ($result === false) {
            throw new TaskNotFoundException();
        }

        /** @var TaskData $task */
        $task = $this->denormalizer->denormalize($result, TaskData::class, null, ['disable_type_enforcement' => true]);

        return $task;
    }
}
