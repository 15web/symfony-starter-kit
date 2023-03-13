<?php

declare(strict_types=1);

namespace App\Task\Console;

use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserId;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserIdQuery;
use App\User\SignUp\Query\FindAllUsers;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Консольная команда отправки пользователю списка невыполненных задач
 */
#[AsCommand(name: 'app:task:send-uncompleted', description: 'Отправляет пользователю список невыполненных задач')]
final class SendUncompletedTaskToUser extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly FindUncompletedTasksByUserId $findUncompletedTasksByUserId,
        private readonly TranslatorInterface $translator,
        private readonly FindAllUsers $findAllUsers,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $this->logger->info('Команда по отправке невыполненных задач уже запущена');

            return Command::SUCCESS;
        }

        $users = ($this->findAllUsers)();
        $emailSent = 0;
        foreach ($users as $user) {
            $uncompletedTasks = ($this->findUncompletedTasksByUserId)(new FindUncompletedTasksByUserIdQuery($user->id));
            if ($uncompletedTasks === []) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->to($user->email)
                ->subject($this->translator->trans('task.uncompleted_tasks_subject'))
                ->htmlTemplate('emails/uncompleted-tasks.html.twig')
                ->context([
                    'tasks' => $uncompletedTasks,
                ]);

            $this->mailer->send($email);
            ++$emailSent;
        }

        $this->logger->info("Отправлено {$emailSent} писем о невыполненных задачах");

        $this->release();

        return Command::SUCCESS;
    }
}
