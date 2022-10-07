<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

interface GetSecurityUserRoles
{
    /**
     * @return array<string>
     */
    public function __invoke(string $identifier): array;
}
