<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Export;

use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\User\Domain\UserId;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Экспорт задач пользователя в заданном формате
 */
final class ExportTasks
{
    /**
     * @var array<string, Exporter>
     */
    private array $exporters;

    /**
     * @param non-empty-list<Exporter> $exporters
     */
    public function __construct(
        private readonly FindAllTasksByUserId $findAllTasksByUserId,
        #[AutowireIterator(Exporter::class)]
        iterable $exporters,
    ) {
        $this->exporters = [];

        foreach ($exporters as $exporter) {
            $this->exporters[$exporter->getFormat()->value] = $exporter;
        }
    }

    /**
     * @throws NotFoundTasksForExportException
     */
    public function __invoke(Format $format, UserId $userId, int $limit = 10, int $offset = 0): BinaryFileResponse
    {
        if (!\array_key_exists($format->value, $this->exporters)) {
            throw new RuntimeException('Не найден обработчик');
        }

        $tasks = ($this->findAllTasksByUserId)(
            query: new FindAllTasksByUserIdQuery(
                userId: $userId->value,
                limit: $limit,
                offset: $offset,
            )
        );

        if ($tasks === []) {
            throw new NotFoundTasksForExportException('Нет задач для экспорта');
        }

        return $this->exporters[$format->value]->export($tasks);
    }
}
