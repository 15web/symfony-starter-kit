<?php

declare(strict_types=1);

namespace App\Mailer\Notification\PasswordRecovery;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Отправляет токен для восстановления пароля
 */
#[AsMessageHandler]
final readonly class SendPasswordRecoveryToken
{
    public function __construct(private MailerInterface $mailer) {}

    public function __invoke(RecoveryPasswordMessage $message): void
    {
        $subject = 'Восстановление пароля';

        $email = new TemplatedEmail()
            ->to($message->email->value)
            ->subject($subject)
            ->htmlTemplate('@mails/emails/recoverPassword.html.twig')
            ->context([
                'recoverToken' => $message->token,
            ]);

        $email->getHeaders()->addTextHeader('recoverToken', (string) $message->token);

        $this->mailer->send($email);
    }
}
