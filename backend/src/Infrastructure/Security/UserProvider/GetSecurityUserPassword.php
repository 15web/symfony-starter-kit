<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

interface GetSecurityUserPassword
{
    public function __invoke(string $identifier): ?string;
}
