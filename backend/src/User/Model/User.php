<?php

declare(strict_types=1);

namespace App\User\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Entity]
/** @final */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    /**
     * Захешированный пароль
     */
    #[ORM\Column]
    private ?string $password;

    #[ORM\Embedded]
    private UserEmail $userEmail;

    #[ORM\Column]
    private UserRole $userRole;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(UserEmail $userEmail)
    {
        $this->id = Uuid::v4();
        $this->userEmail = $userEmail;
        $this->password = null;
        $this->userRole = UserRole::User;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getRoles(): array
    {
        return [$this->userRole->value];
    }

    public function applyPassword(string $password): void
    {
        Assert::notEmpty($password);
        $this->password = $password;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userEmail->getValue();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserEmail(): UserEmail
    {
        return $this->userEmail;
    }

    public function getUserRole(): UserRole
    {
        return $this->userRole;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
