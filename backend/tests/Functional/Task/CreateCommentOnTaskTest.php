<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\DataFixtures\UserFixtures;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class CreateCommentOnTaskTest extends ApiWebTestCase
{
    public function testSuccessfulCreationTask(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

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

    public function testNotFound(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertNotFound($response);
    }

    public function testAddCommentToCompletedTask(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
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
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    public function testNoAccessAnotherUser(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = UserFixtures::SECOND_USER_TOKEN;
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);

        self::assertNotFound($response);
    }
}
