<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Domain\Task;
use App\User\Domain\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final class TaskArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
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
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        /** @var string|null $taskId */
        $taskId = $request->attributes->get('id');
        if ($taskId === null) {
            throw new ApiBadRequestException('Укажите id');
        }

        try {
            Assert::uuid($taskId, 'Укажите валидный id');

            /** @var ?Task $task */
            $task = $this->entityManager->getRepository(Task::class)->find(Uuid::fromString($taskId));

            if ($task === null) {
                throw new ApiNotFoundException('Задача не найдена');
            }
        } catch (\InvalidArgumentException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        if ($task->isBelongToUser($user->getId()) === false) {
            throw new ApiNotFoundException('Задача не найдена');
        }

        yield $task;
    }
}
