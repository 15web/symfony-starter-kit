<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

use Symfony\Component\Uid\Uuid;

interface GetSecurityUserId
{
    public function __invoke(string $identifier): Uuid;
}
