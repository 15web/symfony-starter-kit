<?php

declare(strict_types=1);

/**
 * TODO: где-то мы свернули не туда
 *      - App\User\User странный неймспейс
 *      - в App\User появился не очень доменный Service
 *      - по сути App\User\User\{Domain, Http, Query} тоже можно вынести в App\User
 */
namespace App\User\User\Domain;

use App\Infrastructure\ValueObject\Email;
use App\User\User\Domain\Exception\EmailAlreadyIsConfirmedException;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Пользователь
 */
#[ORM\Entity]
/** @final */
class User
{
    #[ORM\Embedded]
    public readonly Email $userEmail;

    #[ORM\Embedded]
    public readonly ConfirmToken $confirmToken;

    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    /**
     * Захешированный пароль
     */
    #[ORM\Embedded]
    private UserPassword $userPassword;

    #[ORM\Column]
    private UserRole $userRole;

    #[ORM\Column]
    private bool $isConfirmed;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(UserId $userId, Email $userEmail, ConfirmToken $confirmToken, UserRole $userRole)
    {
        $this->id = $userId->value;
        $this->userEmail = $userEmail;
        $this->confirmToken = $confirmToken;
        $this->userRole = $userRole;
        $this->userPassword = new UserPassword('empty');

        $this->isConfirmed = false;
        $this->createdAt = new DateTimeImmutable();
    }

    public function applyHashedPassword(UserPassword $userPassword): void
    {
        Assert::false($this->userPassword->equalTo($userPassword));

        $this->userPassword = $userPassword;
    }

    public function confirm(): void
    {
        // TODO: здесь бы грохнуть ConfirmToken

        if ($this->isConfirmed) {
            throw new EmailAlreadyIsConfirmedException('Email уже подтвержден');

            // или здесь
        }

        // или здесь

        $this->isConfirmed = true;
    }
}
