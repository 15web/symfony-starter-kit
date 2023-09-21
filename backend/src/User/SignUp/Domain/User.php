<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\Infrastructure\ValueObject\Email;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Пользователь
 */
#[ORM\Entity]
/** @final */
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
        if ($this->isConfirmed) {
            throw new EmailAlreadyIsConfirmedException('user.exception.email_already_is_confirmed');
        }

        $this->isConfirmed = true;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function getPassword(): ?string
    {
        return $this->userPassword->value;
    }

    public function getRoles(): array
    {
        return [$this->userRole->value];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userEmail->value;
    }

    public function getUserId(): UserId
    {
        return new UserId($this->id);
    }
}
