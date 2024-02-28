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
#[TestDox('Комментирование задачи')]
final class CreateCommentOnTaskTest extends ApiWebTestCase
{
    #[TestDox('Комментарий добавлен')]
    public function testSuccessfulCreationTask(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('First task', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertSuccessResponse($response);

        $response = self::request(Request::METHOD_GET, "/api/tasks/{$taskId}/comments", token: $token);

        /** @var array{
         *     data: array<int, array{
         *     id: string,
         *     body: string,
         *     createdAt: string,
         *     updatedAt: string|null,
         *    }>
         * } $comments */
        $comments = self::jsonDecode($response->getContent());

        self::assertCount(1, $comments['data']);
        self::assertSame($commentText, $comments['data'][0]['body']);
        self::assertNotEmpty($comments['data'][0]['id']);
        self::assertNotEmpty($comments['data'][0]['createdAt']);
        self::assertNull($comments['data'][0]['updatedAt']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertNotFound($response);
    }

    #[TestDox('Нельзя комментировать выполненную задачу')]
    public function testAddCommentToCompletedTask(): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('First task', $token);

        self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/complete", token: $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertBadRequest($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/add-comment", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    #[TestDox('Комментирование разрешено только автору')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, "/api/tasks/{$taskId}/add-comment", $body, token: $token);

        self::assertNotFound($response);
    }
}
