<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Notification;

use App\Infrastructure\AsService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsService]
#[AsMessageHandler]
final class SendRecoverPasswordToken
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(RecoverPasswordMessage $message): void
    {
        $subject = 'Восстановление пароля';

        $email = (new TemplatedEmail())
            ->to($message->email)
            ->subject($subject)
            ->htmlTemplate('emails/recoverPassword.html.twig')
            ->context([
                'recoverToken' => $message->recoverToken->value,
            ]);

        $email->getHeaders()->addTextHeader('recoverToken', (string) $message->recoverToken->value);

        $this->mailer->send($email);
    }
}
