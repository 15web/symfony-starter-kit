<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Xml;

use App\Infrastructure\AsService;
use App\Task\Http\Export\Exporter;
use App\Task\Http\Export\Format;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Экспорт задач в формат xml
 */
#[AsService]
final readonly class XmlExporter implements Exporter
{
    public function __construct(
        private SerializerInterface $serializer,
        private Filesystem $filesystem,
    ) {}

    #[Override]
    public function getFormat(): Format
    {
        return Format::XML;
    }

    /**
     * @param TaskData[] $tasks
     */
    #[Override]
    public function export(array $tasks): BinaryFileResponse
    {
        $xmlTaskData = $this->serializer->serialize(
            data: $this->adaptData($tasks),
            format: XmlEncoder::FORMAT,
        );

        $file = $this->filesystem->tempnam('/tmp', 'user_');
        file_put_contents($file, $xmlTaskData);

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $response->getFile()->getMimeType());
        $response->setContentDisposition(
            disposition: ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            filename: 'tasks.xml',
        );

        return $response;
    }

    /**
     * @param TaskData[] $tasks
     *
     * @return iterable<XmlTaskData>
     */
    private function adaptData(array $tasks): iterable
    {
        foreach ($tasks as $task) {
            yield new XmlTaskData(
                id: $task->id,
                createdAt: $task->createdAt,
                taskName: $task->taskName,
                isCompleted: $task->isCompleted,
            );
        }
    }
}
