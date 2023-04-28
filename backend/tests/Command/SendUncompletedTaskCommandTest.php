<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[TestDox('Консольная команда для отправки письма о незавершенных задачах')]
final class SendUncompletedTaskCommandTest extends KernelTestCase
{
    #[TestDox('Отправлено письмо с 1 незавершенной задачей')]
    public function testExecute(): void
    {
        $token = User::auth();
        Task::create($taskName1 = 'Тестовая задача для отправки писем №1', $token);

        $taskId2 = Task::createAndReturnId('Тестовая задача для отправки писем №2', $token);
        ApiWebTestCase::request('POST', "/api/tasks/{$taskId2}/complete", token: $token);

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:task:send-uncompleted');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        self::assertEmailCount(1);

        $email = self::getMailerMessage();

        self::assertEmailTextBodyContains($email, $taskName1);
        self::assertEmailHtmlBodyContains($email, $taskName1);
    }
}
