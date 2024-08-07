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
#[TestDox('Выполнение задачи')]
final class CompleteTaskTest extends ApiWebTestCase
{
    #[TestDox('Задача выполнена')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/complete", token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $token);

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

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/complete", token: $token);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/complete", token: $token);
        self::assertBadRequest($response);
    }

    #[TestDox('Задача не найдена')]
    public function testTaskNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/complete", token: $token);
        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request(
            Request::METHOD_POST,
            "/api/tasks/{$taskId}/complete",
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Выполнить задачу может только автор')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request(
            Request::METHOD_POST,
            "/api/tasks/{$taskId}/complete",
            token: $token,
        );

        self::assertNotFound($response);
    }
}
