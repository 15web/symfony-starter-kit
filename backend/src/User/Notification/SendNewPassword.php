<?php

declare(strict_types=1);

namespace App\User\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendNewPassword
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(NewPasswordMessage $message): void
    {
        $subject = 'Ваш пароль';

        $email = (new TemplatedEmail())
            ->to($message->getEmail())
            ->subject($subject)
            ->htmlTemplate('emails/new-password.html.twig')
            ->context([
                'password' => $message->getPlaintextPassword(),
            ]);

        $this->mailer->send($email);
    }
}
