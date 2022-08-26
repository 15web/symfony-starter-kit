<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Task\Query\Task\FindAllByUserId\TaskData;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Autoconfigure(tags: ['app.task.exporter'])]
interface Exporter
{
    public function support(Format $format): bool;

    /**
     * @param TaskData[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse;
}
