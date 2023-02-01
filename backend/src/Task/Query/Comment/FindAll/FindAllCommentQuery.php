<?php

declare(strict_types=1);

namespace App\Task\Query\Comment\FindAll;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос нахождения всех комментариев задачи по пользователю
 */
final class FindAllCommentQuery
{
    public function __construct(public readonly Uuid $taskId, public readonly Uuid $userId)
    {
    }
}
