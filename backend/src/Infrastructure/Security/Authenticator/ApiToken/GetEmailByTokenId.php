<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Authenticator\ApiToken;

use Symfony\Component\Uid\Uuid;

interface GetEmailByTokenId
{
    public function __invoke(Uuid $tokenId): string;
}
