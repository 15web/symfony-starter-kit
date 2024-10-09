<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task\Scheduler;

use App\Task\Scheduler\SendUncompletedTaskToUserScheduler;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Шедулер для отправки письма о незавершенных задачах')]
final class SendUncompletedTaskToUserSchedulerTest extends ApiWebTestCase
{
    #[TestDox('Отправлено письмо с 1 незавершенной задачей')]
    public function testMessageSent(): void
    {
        $token = User::auth();
        Task::create($taskName1 = 'Тестовая задача для отправки писем №1', $token);

        $taskId2 = Task::createAndReturnId('Тестовая задача для отправки писем №2', $token);
        self::request(
            method: Request::METHOD_POST,
            uri: "/api/tasks/{$taskId2}/complete",
            token: $token,
        );

        /**
         * @var SendUncompletedTaskToUserScheduler $scheduler
         */
        $scheduler = self::getContainer()->get(SendUncompletedTaskToUserScheduler::class);
        ($scheduler)();

        self::assertEmailCount(1);

        $email = self::getMailerMessage();

        self::assertNotNull($email);
        self::assertEmailTextBodyContains($email, $taskName1);
        self::assertEmailHtmlBodyContains($email, $taskName1);
    }

    #[TestDox('Незавершенные задачи не найдены, сообщение не отправлено')]
    public function testNoUncompletedTasks(): void
    {
        $token = User::auth();

        $taskId1 = Task::createAndReturnId('Тестовая задача для отправки писем №1', $token);
        self::request(
            method: Request::METHOD_POST,
            uri: "/api/tasks/{$taskId1}/complete",
            token: $token,
        );

        $taskId2 = Task::createAndReturnId('Тестовая задача для отправки писем №2', $token);
        self::request(
            method: Request::METHOD_POST,
            uri: "/api/tasks/{$taskId2}/complete",
            token: $token,
        );

        /**
         * @var SendUncompletedTaskToUserScheduler $scheduler
         */
        $scheduler = self::getContainer()->get(SendUncompletedTaskToUserScheduler::class);
        ($scheduler)();

        self::assertEmailCount(0);
    }
}
