<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Export;

use App\Task\Query\Task\FindAllByUserId\TaskData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Интерфейс экспорта
 */
#[AutoconfigureTag]
interface Exporter
{
    public function getFormat(): Format;

    /**
     * @param TaskData[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse;
}
