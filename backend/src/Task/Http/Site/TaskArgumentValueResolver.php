<?php

declare(strict_types=1);

namespace App\Task\Http\Site;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskRepository;
use InvalidArgumentException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\Uuid;

/**
 * Резолвер нахождения задачи по айди
 */
final readonly class TaskArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {}

    /**
     * @return iterable<Task>
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Task::class) {
            return [];
        }

        /** @var string|null $taskId */
        $taskId = $request->attributes->get('id');
        if ($taskId === null) {
            throw new ApiBadRequestException(['Укажите id']);
        }

        try {
            $task = $this->taskRepository->findById(
                taskId: new TaskId(Uuid::fromString($taskId)),
            );

            if ($task === null) {
                throw new ApiNotFoundException(['Задача не найдена']);
            }
        } catch (InvalidArgumentException) {
            throw new ApiBadRequestException(['Укажите валидный id']);
        }

        return [$task];
    }
}
