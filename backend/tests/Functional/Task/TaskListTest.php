<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Список задач')]
final class TaskListTest extends ApiWebTestCase
{
    private const TASK_DATA_KEY = 'data';
    private const PAGINATION_KEY = 'pagination';
    private const TOTAL = 'total';

    #[TestDox('Получение списка из 2 созданных задач')]
    public function testSuccess(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $response = Task::list($token);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertCount(2, $tasks);
        self::assertSame(2, $pagination[self::TOTAL]);

        foreach ($tasks as $task) {
            self::assertNotNull($task['id']);
            self::assertFalse($task['isCompleted']);
            self::assertNotNull($task['taskName']);
        }
    }

    #[TestDox('Пустой запрос')]
    public function testEmptyResult(): void
    {
        $token = User::auth();

        $response = Task::list($token);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertCount(0, $tasks);
        self::assertSame(0, $pagination[self::TOTAL]);
    }

    #[TestDox('Создано 2 статьи, limit = 1, получена 1 статья')]
    public function testLimit(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $response = Task::list($token, 1);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertCount(1, $tasks);
        self::assertSame(2, $pagination[self::TOTAL]);
    }

    #[TestDox('Создано 2 статьи, limit = 10, offset = 3, получено 0 статей')]
    public function testOffsetEmptyResult(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $response = Task::list($token, 10, 3);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertCount(0, $tasks);
        self::assertSame(2, $pagination[self::TOTAL]);
    }

    #[TestDox('Создано 3 статьи, limit = 1, offset = 2, получена 1 статья')]
    public function testOffsetNotEmptyResult(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        $response = Task::list($token, 1, 2);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertCount(1, $tasks);
        self::assertSame(3, $pagination[self::TOTAL]);
    }

    #[TestDox('Доступ разрешен только автору')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId3 = Task::createAndReturnId($taskName3 = 'Тестовая задача 3', $tokenSecond);
        $taskId4 = Task::createAndReturnId($taskName4 = 'Тестовая задача 4', $tokenSecond);

        $response = Task::list($token);
        $tasks = $response[self::TASK_DATA_KEY];
        $pagination = $response[self::PAGINATION_KEY];

        self::assertSame(2, $pagination[self::TOTAL]);

        foreach ($tasks as $task) {
            self::assertNotSame($task['id'], $taskId3);
            self::assertNotSame($task['id'], $taskId4);
            self::assertNotSame($task['taskName'], $taskName3);
            self::assertNotSame($task['taskName'], $taskName4);
        }
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request(Request::METHOD_GET, '/api/tasks', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
