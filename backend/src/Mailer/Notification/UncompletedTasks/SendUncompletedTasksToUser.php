<?php

declare(strict_types=1);

namespace App\Mailer\Notification\UncompletedTasks;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Отправляет пользователю список невыполненных задач
 */
#[AsMessageHandler]
final readonly class SendUncompletedTasksToUser
{
    public function __construct(private MailerInterface $mailer) {}

    public function __invoke(UncompletedTasksMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->to($message->email->value)
            ->subject('Невыполненные задачи')
            ->htmlTemplate('@mails/emails/uncompleted-tasks.html.twig')
            ->context([
                'tasks' => $message->tasks,
            ]);

        $this->mailer->send($email);
    }
}
