<?php

declare(strict_types=1);

namespace App\Task\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\Task;
use App\Task\Domain\Tasks;

#[AsService]
final class RemoveTask
{
    public function __construct(private readonly Flush $flush, private readonly Tasks $tasks)
    {
    }

    public function __invoke(Task $task): void
    {
        $this->tasks->remove($task);
        ($this->flush)();
    }
}
