<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        /**
         * @var array<string> $roles
         */
        private readonly array $roles,
        private readonly string $userIdentifier,
        private readonly ?string $password,
        private readonly Uuid $id,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
