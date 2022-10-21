<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

use App\Infrastructure\AsService;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[AsService]
final class SecurityUserProvider implements UserProviderInterface
{
    private ?SecurityUser $securityUser;

    public function __construct(
        private readonly GetSecurityUserRoles $getSecurityUserRoles,
        private readonly GetSecurityUserPassword $getSecurityUserPassword,
        private readonly GetSecurityUserId $getSecurityUserId,
    ) {
        $this->securityUser = null;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->securityUser ?? $this->getSecurityUser($identifier);
    }

    public function refreshUser(UserInterface $user): SecurityUser
    {
        $this->securityUser = $this->getSecurityUser($user->getUserIdentifier());

        return $this->securityUser;
    }

    public function supportsClass(string $class): bool
    {
        return $class === SecurityUser::class;
    }

    private function getSecurityUser(string $identifier): SecurityUser
    {
        try {
            $securityUserId = ($this->getSecurityUserId)($identifier);
            $securityUserPassword = ($this->getSecurityUserPassword)($identifier);
            $securityUserRoles = ($this->getSecurityUserRoles)($identifier);

            return new SecurityUser($securityUserRoles, $identifier, $securityUserPassword, $securityUserId);
        } catch (\Throwable $e) {
            throw new UserNotFoundException(previous: $e);
        }
    }
}
