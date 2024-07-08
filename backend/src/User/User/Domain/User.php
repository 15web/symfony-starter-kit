<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use App\Infrastructure\ValueObject\Email;
use App\User\User\Domain\Exception\EmailAlreadyIsConfirmedException;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @final
 *
 * Пользователь
 */
#[ORM\Entity, ORM\Table(name: '"user"')]
class User
{
    #[ORM\Embedded]
    public readonly Email $userEmail;

    #[ORM\Embedded]
    public ?ConfirmToken $confirmToken;

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

        $this->userPassword = new UserPassword(
            cleanPassword: bin2hex(random_bytes(6)),
            hashCost: 4,
        );

        $this->isConfirmed = false;
        $this->createdAt = new DateTimeImmutable();
    }

    public function applyPassword(UserPassword $userPassword): void
    {
        $this->userPassword = $userPassword;
    }

    public function confirm(): void
    {
        if ($this->isConfirmed) {
            throw new EmailAlreadyIsConfirmedException('Email уже подтвержден');
        }

        $this->isConfirmed = true;
        $this->confirmToken = null;
    }
}
