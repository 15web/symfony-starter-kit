<?php

declare(strict_types=1);

namespace App\Mailer\Notification\EmailConfirmation;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Отправляет токен на почту для подтверждения
 */
#[AsMessageHandler]
final readonly class SendEmailConfirmToken
{
    public function __construct(private MailerInterface $mailer) {}

    public function __invoke(ConfirmEmailMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->to($message->email->value)
            ->subject('Подтверждение email')
            ->htmlTemplate('@mails/emails/confirm.html.twig')
            ->context([
                'confirmToken' => $message->confirmToken,
            ]);

        $email->getHeaders()->addTextHeader('confirmToken', (string) $message->confirmToken);

        $this->mailer->send($email);
    }
}
