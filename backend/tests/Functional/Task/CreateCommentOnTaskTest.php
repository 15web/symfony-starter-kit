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
 * @testdox Функциональый тест комментирования сущности Task
 */
final class CreateCommentOnTaskTest extends ApiWebTestCase
{
    /**
     * @testdox Комментарий добавлен
     */
    public function testSuccessfulCreationTask(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('First task', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertSuccessResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}/comments", token: $token);
        $comments = self::jsonDecode($response->getContent());

        self::assertCount(1, $comments);
        self::assertSame($commentText, $comments[0]['body']);
        self::assertNotNull($comments[0]['id']);
        self::assertNotNull($comments[0]['createdAt']);
        self::assertNull($comments[0]['updatedAt']);
    }

    /**
     * @testdox Task не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertNotFound($response);
    }

    /**
     * @testdox Нельзя комментировать завершенный Task
     */
    public function testAddCommentToCompletedTask(): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('First task', $token);

        self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertBadRequest($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен, невалидный токен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @testdox Комментирование запрещено для пользователя не автора
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);

        self::assertNotFound($response);
    }
}
