<?php

declare(strict_types=1);

namespace App\User\Security\Http;

use App\User\User\Domain\UserRole;
use Attribute;

/**
 * Контроллеры, помеченные данным атрибутом, будут требовать аутентификацию и авторизацию
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class IsGranted
{
    public function __construct(public UserRole $userRole) {}
}
