<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\AsService;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

#[AsService]
final class CreatePasswordHasher
{
    public function __invoke(string $plaintextPassword): string
    {
        return (new NativePasswordHasher())->hash($plaintextPassword);
    }
}
