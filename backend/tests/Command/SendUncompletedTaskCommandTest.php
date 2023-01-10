<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class SendUncompletedTaskCommandTest extends KernelTestCase
{
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
