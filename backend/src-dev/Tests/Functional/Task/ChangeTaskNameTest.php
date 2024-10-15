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
#[TestDox('Смена наименования задачи')]
final class ChangeTaskNameTest extends ApiWebTestCase
{
    #[TestDox('Имя изменено')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $body = [];
        $body['taskName'] = $secondName = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/update-task-name', $taskId),
            body: $body,
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

        self::assertSame($secondName, $task['data']['taskName']);
        self::assertNotNull($task['data']['updatedAt']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();
        Task::create(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/update-task-name', $taskId),
            body: $body,
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[TestDox('Неправильный запрос')]
    public function testBadRequests(): void
    {
        $token = User::auth();
        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['taskName' => ''], $token);
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

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/update-task-name', $taskId),
            body: $body,
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Менять наименование разрешено только автору задачи')]
    public function testNoAccessAnotherUser(): void
    {
        $userToken = User::auth();
        Task::create('Тестовая задача №1', $userToken);

        $anotherUserToken = User::auth('second@example.com');
        $anotherTaskId = Task::createAndReturnId(
            taskName: 'Тестовая задача №2 ',
            token: $anotherUserToken,
        );

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/update-task-name', $anotherTaskId),
            body: $body,
            token: $userToken,
        );

        self::assertNotFound($response);
    }

    /**
     * @param array<int|string> $body
     */
    private function assertBadRequests(array $body, string $token): void
    {
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/update-task-name', $taskId),
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );
        self::assertBadRequest($response);
    }
}
