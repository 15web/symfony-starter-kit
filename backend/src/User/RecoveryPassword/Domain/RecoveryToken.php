<?php

declare(strict_types=1);

namespace App\User\RecoveryPassword\Domain;

use App\User\User\Domain\UserId;
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
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Column(type: 'uuid')]
    private Uuid $token;

    public function __construct(Uuid $id, UserId $userId, Uuid $token)
    {
        $this->id = $id;
        $this->userId = $userId->value;
        $this->token = $token;
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
