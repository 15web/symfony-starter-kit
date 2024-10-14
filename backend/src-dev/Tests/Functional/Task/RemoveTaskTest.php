<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Удаление задачи')]
final class RemoveTaskTest extends ApiWebTestCase
{
    #[TestDox('Задача удалена')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $task1Id = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $task2Id = Task::createAndReturnId(
            taskName: 'Тестовая задача 2',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/tasks/%s', $task1Id),
            token: $token,
        );

        self::assertSuccessContentResponse($response);

        $response = Task::list($token);
        $tasks = $response['data'];

        self::assertCount(1, $tasks);
        self::assertSame($task2Id, $tasks[0]['id']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $taskId = (string) Uuid::v4();
        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/tasks/%s', $taskId),
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/tasks/%s', $taskId),
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Удалять может только автор задачи')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create(
            taskName: 'Тестовая задача №1',
            token: $token,
        );

        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача №2 ',
            token: $tokenSecond,
        );

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: \sprintf('/api/tasks/%s', $taskId),
            token: $token,
        );

        self::assertNotFound($response);
    }
}
