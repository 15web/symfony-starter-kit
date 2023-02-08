<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Удаление задачи
 */
final class RemoveTaskTest extends ApiWebTestCase
{
    /**
     * @testdox Задача удалена
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        $task1Id = Task::createAndReturnId('Тестовая задача 1', $token);
        $task2Id = Task::createAndReturnId('Тестовая задача 2', $token);

        $response = self::request('POST', "/api/tasks/{$task1Id}/remove", token: $token);
        self::assertSuccessContentResponse($response);

        $response = Task::list($token);
        $tasks = $response['data'];

        self::assertCount(1, $tasks);
        self::assertSame($task2Id, $tasks[0]['id']);
    }

    /**
     * @testdox Задача не найдена
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @testdox Удалять может только автор задачи
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request('POST', "/api/tasks/{$taskId}/remove", token: $token);

        self::assertNotFound($response);
    }
}
