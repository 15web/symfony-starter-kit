<?php

declare(strict_types=1);

namespace App\User\Profile\Query\FindByUserId;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос на нахождение профиля по пользователю
 */
final readonly class FindProfileByUserIdQuery
{
    public function __construct(public Uuid $userId) {}
}
