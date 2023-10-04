<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\Tasks;
use App\User\SignUp\Domain\User;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Резолвер нахождения задачи по айди и пользователю
 */
#[AsService]
final readonly class TaskArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(
        private Security $security,
        private Tasks $tasks,
    ) {}

    /**
     * @return iterable<Task>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Task::class) {
            return [];
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new ApiUnauthorizedException(['Необходимо пройти аутентификацию']);
        }

        /** @var string|null $taskId */
        $taskId = $request->attributes->get('id');
        if ($taskId === null) {
            throw new ApiBadRequestException(['Укажите id']);
        }

        try {
            Assert::uuid($taskId, 'Укажите валидный id');

            $task = $this->tasks->findByIdAndUserId(
                taskId: new TaskId(Uuid::fromString($taskId)),
                userId: $user->getUserId(),
            );

            if ($task === null) {
                throw new ApiNotFoundException(['Задача не найдена']);
            }
        } catch (InvalidArgumentException $exception) {
            throw new ApiBadRequestException([$exception->getMessage()]);
        }

        return [$task];
    }
}
