<?php

declare(strict_types=1);

namespace App\User\User\Query;

use App\Infrastructure\ValueObject\Email;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

/**
 * DTO пользователя
 */
final readonly class UserListData
{
    public Email $email;

    /**
     * @param non-empty-string $email
     */
    public function __construct(
        public Uuid $id,
        #[SensitiveParameter]
        string $email,
    ) {
        $this->email = new Email($email);
    }
}
