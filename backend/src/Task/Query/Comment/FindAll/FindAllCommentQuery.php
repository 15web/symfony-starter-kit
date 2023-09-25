<?php

declare(strict_types=1);

namespace App\Task\Query\Comment\FindAll;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос нахождения всех комментариев задачи по пользователю
 */
final readonly class FindAllCommentQuery
{
    public function __construct(public Uuid $taskId, public Uuid $userId) {}
}
