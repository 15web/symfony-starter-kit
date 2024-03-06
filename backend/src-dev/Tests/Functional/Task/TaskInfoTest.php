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
#[TestDox('Получение информации по задаче')]
final class TaskInfoTest extends ApiWebTestCase
{
    #[TestDox('Создана задача, получена по ней информация')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId($taskName = 'Тестовая задача 1', $token);

        $response = Task::list($token);
        $tasks = $response['data'];

        self::assertCount(1, $tasks);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $token);
        self::assertSuccessResponse($response);

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

        self::assertNotNull($task['data']['id']);
        self::assertSame($taskName, $task['data']['taskName']);
        self::assertFalse($task['data']['isCompleted']);
        self::assertNotNull($task['data']['createdAt']);
        self::assertNull($task['data']['completedAt']);
        self::assertNull($task['data']['updatedAt']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $token);
        self::assertNotFound($response);
    }

    #[TestDox('Получение информации по задаче доступно только автору')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $token);
        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
