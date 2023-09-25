<?php

declare(strict_types=1);

namespace App\Mailer\Notification\UncompletedTasks;

use App\Infrastructure\AsService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Отправляет пользователю список невыполненных задач
 */
#[AsService]
#[AsMessageHandler]
final readonly class SendUncompletedTasksToUser
{
    public function __construct(private MailerInterface $mailer, private TranslatorInterface $translator) {}

    public function __invoke(UncompletedTasksMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->to($message->email)
            ->subject($this->translator->trans('task.uncompleted_tasks_subject'))
            ->htmlTemplate('@mails/emails/uncompleted-tasks.html.twig')
            ->context([
                'tasks' => $message->tasks,
            ]);

        $this->mailer->send($email);
    }
}
