<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Domain;

use App\User\SignUp\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Восстановление пароля
 */
#[ORM\Entity]
#[ORM\Index(fields: ['userId'], name: 'user_id_idx')]
/** @final */
class RecoveryToken
{
    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    public function __construct(
        #[ORM\Id,
            ORM\Column(type: 'uuid', unique: true)]
        private readonly Uuid $id,
        UserId $userId,
        #[ORM\Column(type: 'uuid')]
        private Uuid $token
    ) {
        $this->userId = $userId->value;
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }

    public function getToken(): Uuid
    {
        return $this->token;
    }
}
