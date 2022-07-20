<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Csv;

use App\Task\Http\Export\Exporter;
use App\Task\Http\Export\Format;
use App\Task\Model\Task;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class CsvExporter implements Exporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function support(Format $format): bool
    {
        return $format === Format::CSV;
    }

    /**
     * @param Task[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse
    {
        $csvTaskData = $this->serializer->serialize($this->adaptData($tasks), CsvEncoder::FORMAT);

        $file = $this->filesystem->tempnam('/tmp', 'user_');
        file_put_contents($file, $csvTaskData);

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'tasks.csv');

        return $response;
    }

    /**
     * @param Task[] $tasks
     *
     * @return \Generator<CsvTaskData>
     */
    private function adaptData(array $tasks): \Generator
    {
        foreach ($tasks as $task) {
            yield new CsvTaskData(
                $task->getId(),
                $task->getCreatedAt(),
                $task->getCompletedAt(),
                $task->getTaskName()->getValue(),
                $task->isCompleted(),
            );
        }
    }
}
