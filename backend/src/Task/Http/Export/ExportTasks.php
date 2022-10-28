<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Infrastructure\AsService;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[AsService]
final class ExportTasks
{
    /**
     * @param Exporter[] $exporters
     */
    public function __construct(
        private readonly FindAllTasksByUserId $findAllTasksByUserId,
        #[TaggedIterator(tag: 'app.task.exporter')] private readonly iterable $exporters,
    ) {
    }

    public function __invoke(Format $format, UserId $userId): BinaryFileResponse
    {
        $tasks = ($this->findAllTasksByUserId)(new FindAllTasksByUserIdQuery($userId->value));

        foreach ($this->exporters as $exporter) {
            if ($exporter->support($format) === true) {
                return $exporter->export($tasks);
            }
        }

        throw new \RuntimeException('Не найден обработчик');
    }
}
