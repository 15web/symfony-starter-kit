<?php

declare(strict_types=1);

namespace App\User\SignUp\Notification;

use App\Infrastructure\AsService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Отправляет токен на почту для подтверждения
 */
#[AsService]
#[AsMessageHandler]
final class SendConfirmEmailToken
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(ConfirmEmailMessage $message): void
    {
        $subject = 'Подтверждение email';

        $email = (new TemplatedEmail())
            ->to($message->getEmail())
            ->subject($subject)
            ->htmlTemplate('emails/confirm.html.twig')
            ->context([
                'confirmToken' => $message->getConfirmToken(),
            ]);

        $email->getHeaders()->addTextHeader('confirmToken', (string) $message->getConfirmToken());

        $this->mailer->send($email);
    }
}
