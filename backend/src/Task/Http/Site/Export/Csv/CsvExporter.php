<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Export\Csv;

use App\Infrastructure\AsService;
use App\Task\Http\Site\Export\Exporter;
use App\Task\Http\Site\Export\Format;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Экспорт задач в формат csv
 */
#[AsService]
final readonly class CsvExporter implements Exporter
{
    public function __construct(
        private SerializerInterface $serializer,
        private Filesystem $filesystem,
    ) {}

    #[Override]
    public function getFormat(): Format
    {
        return Format::CSV;
    }

    /**
     * @param TaskData[] $tasks
     */
    #[Override]
    public function export(array $tasks): BinaryFileResponse
    {
        $csvTaskData = $this->serializer->serialize(
            data: $this->adaptData($tasks),
            format: CsvEncoder::FORMAT,
        );

        $file = $this->filesystem->tempnam('/tmp', 'user_');
        file_put_contents($file, $csvTaskData);

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'text/csv');
        $response->setContentDisposition(
            disposition: ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            filename: 'tasks.csv',
        );

        return $response;
    }

    /**
     * @param TaskData[] $tasks
     *
     * @return iterable<CsvTaskData>
     */
    private function adaptData(array $tasks): iterable
    {
        foreach ($tasks as $task) {
            yield new CsvTaskData(
                id: $task->id,
                taskName: $task->taskName,
                isCompleted: $task->isCompleted,
            );
        }
    }
}
