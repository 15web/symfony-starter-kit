<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Task\Model\Tasks;
use App\User\Model\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportTasks
{
    /**
     * @param Exporter[] $exporters
     */
    public function __construct(
        private readonly Tasks $tasks,
        private readonly iterable $exporters,
    ) {
    }

    public function __invoke(Format $format, User $user): BinaryFileResponse
    {
        $tasks = $this->tasks->findAllByUserId($user->getId());

        foreach ($this->exporters as $exporter) {
            if ($exporter->support($format) === true) {
                return $exporter->export($tasks);
            }
        }

        throw new \RuntimeException('Не найден обработчик');
    }
}
