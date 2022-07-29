<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Task\Query\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\Model\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportTasks
{
    /**
     * @param Exporter[] $exporters
     */
    public function __construct(
        private readonly FindAllTasksByUserId $findAllTasksByUserId,
        private readonly iterable $exporters,
    ) {
    }

    public function __invoke(Format $format, User $user): BinaryFileResponse
    {
        $tasks = ($this->findAllTasksByUserId)(new FindAllTasksByUserIdQuery($user->getId()));

        foreach ($this->exporters as $exporter) {
            if ($exporter->support($format) === true) {
                return $exporter->export($tasks);
            }
        }

        throw new \RuntimeException('Не найден обработчик');
    }
}
