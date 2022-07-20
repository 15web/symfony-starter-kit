<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Task\Model\Task;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Exporter
{
    public function support(Format $format): bool;

    /**
     * @param Task[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse;
}
