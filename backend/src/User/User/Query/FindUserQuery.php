<?php

declare(strict_types=1);

namespace App\User\User\Query;

use App\Infrastructure\ValueObject\Email;
use App\User\User\Domain\UserId;
use DomainException;
use Symfony\Component\Uid\Uuid;

/**
 * Запрос на нахождение данных пользователя
 */
final readonly class FindUserQuery
{
    public function __construct(
        public ?UserId $userId = null,
        public ?Email $userEmail = null,
        public ?Uuid $confirmToken = null,
    ) {
        if ($userId !== null) {
            return;
        }

        if ($userEmail !== null) {
            return;
        }

        if ($confirmToken !== null) {
            return;
        }

        throw new DomainException();
    }
}
