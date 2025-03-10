<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task\Site;

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
#[TestDox('Выполнение задачи')]
final class CompleteTaskTest extends ApiWebTestCase
{
    #[TestDox('Задача выполнена')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $token,
        );

        self::assertSuccessContentResponse($response);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/tasks/%s', $taskId),
            token: $token,
        );

        /** @var array{
         *     data: array{
         *     id: string|null,
         *     taskName: string,
         *     isCompleted: bool,
         *     createdAt: string|null,
         *     completedAt: string|null,
         *     updatedAt: string|null
         *    }
         * } $task */
        $task = self::jsonDecode($response->getContent());

        self::assertTrue($task['data']['isCompleted']);
        self::assertNotNull($task['data']['updatedAt']);
        self::assertNotNull($task['data']['completedAt']);
    }

    #[TestDox('Нельзя повторно выполнить задачу')]
    public function testTaskHasAlreadyBeenCompleted(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $token,
        );

        self::assertBadRequest($response);
    }

    #[TestDox('Задача не найдена')]
    public function testTaskNotFound(): void
    {
        $token = User::auth();

        Task::create(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $taskId = (string) Uuid::v7();
        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
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
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Администратору разрешен доступ')]
    public function testAdminAccess(): void
    {
        $adminToken = User::auth('admin@example.test');

        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $adminToken,
        );

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $adminToken,
        );

        self::assertSuccessContentResponse($response);
    }

    #[TestDox('Выполнить задачу может только автор')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create(
            taskName: 'Тестовая задача №1',
            token: $token,
        );

        $tokenSecond = User::registerAndAuth('second@example.com');
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача №2 ',
            token: $tokenSecond,
        );

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $token,
        );

        self::assertNotFound($response);
    }
}
