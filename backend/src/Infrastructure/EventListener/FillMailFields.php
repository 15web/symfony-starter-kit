<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Заполняет from секцию и subject поля для отправки сообщений из конфига
 */
#[AsEventListener]
final readonly class FillMailFields
{
    public function __construct(
        #[Autowire('%env(string:MAILER_FROM_EMAIL)%')]
        private string $fromEmail,
        #[Autowire('%env(string:MAILER_FROM_NAME)%')]
        private string $fromName,
    ) {}

    /**
     * Всем письмам добавляет тему письма, почту отправителя
     */
    public function __invoke(MessageEvent $messageEvent): void
    {
        $email = $messageEvent->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($this->fromEmail, $this->fromName));
        $email->subject(\sprintf('%s. %s', $this->fromName, $email->getSubject() ?? ''));
    }
}
