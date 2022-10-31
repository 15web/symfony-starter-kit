<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Task\Query\Task\FindUncompletedTasksByUserId\TaskData;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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

        $this->tearDown();
        $token = User::auth('second@example.com');
        Task::create($taskName3 = 'Тестовая задача для отправки писем №3', $token);

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:task:send-uncompleted');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        self::assertEmailCount(2);

        /** @var TemplatedEmail[] $emailsSent */
        $emailsSent = self::getMailerMessages('null://');
        $taskNamesSent = [];
        foreach ($emailsSent as $email) {
            /** @var TaskData[] $tasksSentToUser */
            $tasksSentToUser = $email->getContext()['tasks'];

            self::assertCount(1, $tasksSentToUser);
            $taskNamesSent[] = $tasksSentToUser[0]->taskName;
        }

        self::assertContains($taskName1, $taskNamesSent);
        self::assertContains($taskName3, $taskNamesSent);
    }
}
