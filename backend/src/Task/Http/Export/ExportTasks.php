<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Infrastructure\AsService;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\Task\FindAllByUserId\FindAllTasksByUserIdQuery;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Экспорт задач пользователя в заданном формате
 */
#[AsService]
final class ExportTasks
{
    /**
     * @var array<string, Exporter>
     */
    private array $exporters;

    /**
     * @param Exporter[] $exporters
     */
    public function __construct(
        private readonly FindAllTasksByUserId $findAllTasksByUserId,
        #[TaggedIterator(tag: 'app.task.exporter')]
        iterable $exporters,
    ) {
        foreach ($exporters as $exporter) {
            $this->exporters[$exporter->getFormat()->value] = $exporter;
        }
    }

    public function __invoke(Format $format, UserId $userId, int $limit = 10, int $offset = 0): BinaryFileResponse
    {
        if (\array_key_exists($format->value, $this->exporters) === false) {
            throw new \RuntimeException('Не найден обработчик');
        }

        $tasks = ($this->findAllTasksByUserId)(new FindAllTasksByUserIdQuery($userId->value, $limit, $offset));

        return $this->exporters[$format->value]->export($tasks);
    }
}
