<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task\Site;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Создание задачи')]
final class CreateTaskTest extends ApiWebTestCase
{
    #[TestDox('Задача создана')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = Task::create(
            taskName: $taskName = 'Тестовая задача',
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array{id: string|null}
         * } $taskInfo */
        $taskInfo = self::jsonDecode($response->getContent());
        self::assertNotNull($taskInfo['data']['id']);

        $response = Task::list($token);
        $tasks = $response['data'];

        self::assertCount(1, $tasks);
        self::assertSame($taskName, $tasks[0]['taskName']);
        self::assertNotEmpty($tasks[0]['id']);
        self::assertSame($taskInfo['data']['id'], $tasks[0]['id']);
        self::assertFalse($tasks[0]['isCompleted']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = Task::create(
            taskName: 'Тестовая задача',
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Администратору разрешен доступ')]
    public function testAdminAccess(): void
    {
        $adminToken = User::auth('admin@example.test');

        $response = Task::create(
            taskName: 'Тестовая задача',
            token: $adminToken,
        );

        self::assertSuccessContentResponse($response);
    }

    #[TestDox('Неверный запрос')]
    public function testBadRequests(): void
    {
        $token = User::auth();

        $this->assertBadRequests([], $token);
        $this->assertBadRequests(['badKey'], $token);
        $this->assertBadRequests(['taskName' => ''], $token);

        $badJson = '{"taskName"=1}';
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/tasks',
            body: $badJson,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    /**
     * @param array<int|string> $body
     */
    private function assertBadRequests(array $body, string $token): void
    {
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/tasks',
            body: $body,
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }
}
