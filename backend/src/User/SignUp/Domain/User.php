<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use App\User\RecoveryPassword\Domain\RecoveryToken;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Entity]
/** @final */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Embedded]
    public readonly UserEmail $userEmail;

    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    /**
     * Захешированный пароль
     */
    #[ORM\Embedded]
    private UserPassword $userPassword;

    #[ORM\Embedded]
    private RecoveryToken $recoveryToken;

    #[ORM\Column]
    private UserRole $userRole;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(UserId $userId, UserEmail $userEmail, UserRole $userRole)
    {
        $this->id = $userId->value;
        $this->userEmail = $userEmail;
        $this->userRole = $userRole;
        $this->userPassword = new UserPassword('empty');

        $this->createdAt = new \DateTimeImmutable();
    }

    public function applyHashedPassword(UserPassword $userPassword): void
    {
        Assert::false($this->userPassword->equalTo($userPassword));

        $this->userPassword = $userPassword;
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

    public function updateRecoveryToken(RecoveryToken $recoveryToken): void
    {
        Assert::false($this->userPassword->equalTo($recoveryToken));

        $this->recoveryToken = $recoveryToken;
    }
}
