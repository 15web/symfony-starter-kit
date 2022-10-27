<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\DataFixtures\UserFixtures;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\Uid\Uuid;

final class CompleteTaskTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        $task = self::jsonDecode($response->getContent());

        self::assertTrue($task['isCompleted']);
        self::assertNotNull($task['updatedAt']);
        self::assertNotNull($task['completedAt']);
    }

    public function testTaskHasAlreadyBeenCompleted(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);

        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertBadRequest($response);
    }

    public function testTaskNotFound(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request(
            'POST',
            "/api/tasks/{$taskId}/complete",
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    public function testNoAccessAnotherUser(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = UserFixtures::SECOND_USER_TOKEN;
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request(
            'POST',
            "/api/tasks/{$taskId}/complete",
            token: $token,
        );

        self::assertNotFound($response);
    }
}
