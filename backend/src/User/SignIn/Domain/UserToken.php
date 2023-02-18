<?php

declare(strict_types=1);

namespace App\User\SignIn\Domain;

use App\User\SignUp\Domain\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Токен пользователя
 */
#[ORM\Entity]
#[ORM\Index(fields: ['userId'], name: 'user_id_idx')]
/** @final */
class UserToken
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private readonly Uuid $userId;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(Uuid $id, UserId $userId)
    {
        $this->id = $id;
        $this->userId = $userId->value;

        $this->createdAt = new DateTimeImmutable();
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }
}
