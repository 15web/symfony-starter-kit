<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Task\Query\FindAllByUserId\TaskData;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Exporter
{
    public function support(Format $format): bool;

    /**
     * @param TaskData[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse;
}
