<?php

declare(strict_types=1);

namespace App\Mailer\Notification\EmailConfirmation;

use App\Infrastructure\AsService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Отправляет токен на почту для подтверждения
 */
#[AsService]
#[AsMessageHandler]
final readonly class SendEmailConfirmToken
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator
    ) {}

    public function __invoke(ConfirmEmailMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->to($message->email->value)
            ->subject($this->translator->trans('user.mail.confirm_email_subject'))
            ->htmlTemplate('@mails/emails/confirm.html.twig')
            ->context([
                'confirmToken' => $message->confirmToken,
            ]);

        $email->getHeaders()->addTextHeader('confirmToken', (string) $message->confirmToken);

        $this->mailer->send($email);
    }
}
