<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\Infrastructure\Security\UserProvider\SecurityUser;
use App\Task\Domain\Task;
use App\Task\Domain\TaskId;
use App\Task\Domain\Tasks;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[AsService]
final class TaskArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly Tasks $tasks,
    ) {
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Task::class;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @return iterable<Task>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var UserInterface|null $user */
        $user = $this->security->getUser();

        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user instanceof SecurityUser === false) {
            throw new \DomainException();
        }

        /** @var SecurityUser $user */

        /** @var string|null $taskId */
        $taskId = $request->attributes->get('id');
        if ($taskId === null) {
            throw new ApiBadRequestException('Укажите id');
        }

        try {
            Assert::uuid($taskId, 'Укажите валидный id');

            $task = $this->tasks->findByIdAndUserId(
                new TaskId(Uuid::fromString($taskId)),
                new UserId($user->getId()),
            );

            if ($task === null) {
                throw new ApiNotFoundException('Задача не найдена');
            }
        } catch (\InvalidArgumentException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        yield $task;
    }
}
