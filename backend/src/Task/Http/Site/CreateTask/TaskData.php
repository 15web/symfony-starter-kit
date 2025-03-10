<?php

declare(strict_types=1);

namespace App\Task\Http\Site\CreateTask;

use Symfony\Component\Uid\Uuid;

/**
 * Ответ после создания задачи
 */
final readonly class TaskData
{
    public function __construct(public Uuid $id) {}
}
