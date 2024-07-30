<?php

declare(strict_types=1);

namespace Dev\Tests\Command;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[TestDox('Консольная команда для отправки письма о незавершенных задачах')]
final class SendUncompletedTaskCommandTest extends ApiWebTestCase
{
    #[TestDox('Отправлено письмо с 1 незавершенной задачей')]
    public function testExecute(): void
    {
        $token = User::auth();
        Task::create($taskName1 = 'Тестовая задача для отправки писем №1', $token);

        $taskId2 = Task::createAndReturnId('Тестовая задача для отправки писем №2', $token);
        self::request(
            method: Request::METHOD_POST,
            uri: "/api/tasks/{$taskId2}/complete",
            token: $token,
        );

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:task:send-uncompleted');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        self::assertEmailCount(1);

        $email = self::getMailerMessage();

        self::assertNotNull($email);
        self::assertEmailTextBodyContains($email, $taskName1);
        self::assertEmailHtmlBodyContains($email, $taskName1);
    }
}
