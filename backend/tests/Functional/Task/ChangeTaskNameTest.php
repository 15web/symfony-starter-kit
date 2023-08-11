<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
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
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = $secondName = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/update-task-name", $body, token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}", token: $token);
        $task = self::jsonDecode($response->getContent());

        self::assertSame($secondName, $task['taskName']);
        self::assertNotNull($task['updatedAt']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/update-task-name", $body, token: $token);
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
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            Request::METHOD_POST,
            "/api/tasks/{$taskId}/update-task-name",
            $body,
            token: $notValidToken
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Менять наименование разрешено только автору задачи')]
    public function testNoAccessAnotherUser(): void
    {
        $userToken = User::auth();
        Task::create('Тестовая задача №1', $userToken);

        $this->tearDown();
        $anotherUserToken = User::auth('second@example.com');
        $anotherTaskId = Task::createAndReturnId('Тестовая задача №2 ', $anotherUserToken);

        $body = [];
        $body['taskName'] = 'Тестовая задача 2';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            Request::METHOD_POST,
            "/api/tasks/{$anotherTaskId}/update-task-name",
            $body,
            token: $userToken
        );

        self::assertNotFound($response);
    }

    private function assertBadRequests(array $body, string $token): void
    {
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            Request::METHOD_POST,
            "/api/tasks/{$taskId}/update-task-name",
            $body,
            token: $token,
            disableValidateRequestSchema: true
        );
        self::assertBadRequest($response);
    }
}
