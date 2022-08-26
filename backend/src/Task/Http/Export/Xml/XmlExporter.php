<?php

declare(strict_types=1);

namespace App\Task\Http\Export\Xml;

use App\AsService;
use App\Task\Http\Export\Exporter;
use App\Task\Http\Export\Format;
use App\Task\Query\Task\FindAllByUserId\TaskData;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsService]
final class XmlExporter implements Exporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function support(Format $format): bool
    {
        return $format === Format::XML;
    }

    /**
     * @param TaskData[] $tasks
     */
    public function export(array $tasks): BinaryFileResponse
    {
        $csvTaskData = $this->serializer->serialize($this->adaptData($tasks), XmlEncoder::FORMAT);

        $file = $this->filesystem->tempnam('/tmp', 'user_');
        file_put_contents($file, $csvTaskData);

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'tasks.xml');

        return $response;
    }

    /**
     * @param TaskData[] $tasks
     *
     * @return \Generator<XmlTaskData>
     */
    private function adaptData(array $tasks): \Generator
    {
        foreach ($tasks as $task) {
            yield new XmlTaskData(
                $task->id,
                $task->createdAt,
                $task->taskName,
                $task->isCompleted,
            );
        }
    }
}
