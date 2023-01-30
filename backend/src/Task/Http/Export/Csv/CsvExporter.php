<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Csv;

use App\Infrastructure\AsService;
use App\Task\Http\Export\Exporter;
use App\Task\Http\Export\Format;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Экспорт задач в формат csv
 */
#[AsService]
final class CsvExporter implements Exporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function getFormat(): Format
    {
        return Format::CSV;
    }

    /**
     * @param TaskData[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse
    {
        $csvTaskData = $this->serializer->serialize($this->adaptData($tasks), CsvEncoder::FORMAT);

        $file = $this->filesystem->tempnam('/tmp', 'user_');
        file_put_contents($file, $csvTaskData);

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'text/csv');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'tasks.csv');

        return $response;
    }

    /**
     * @param TaskData[] $tasks
     *
     * @return \Generator<CsvTaskData>
     */
    private function adaptData(array $tasks): \Generator
    {
        foreach ($tasks as $task) {
            yield new CsvTaskData(
                $task->id,
                $task->taskName,
                $task->isCompleted,
            );
        }
    }
}
