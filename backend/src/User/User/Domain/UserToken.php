<?php

declare(strict_types=1);

namespace App\User\User\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Токен пользователя
 */
#[ORM\Entity]
#[ORM\Index(name: 'ix_user_token_user_id', fields: ['userId'])]
/** @final */
class UserToken
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private readonly Uuid $userId;

    #[ORM\Column]
    private readonly string $hash;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    /**
     * @param non-empty-string $hash
     */
    public function __construct(UserTokenId $id, UserId $userId, string $hash)
    {
        $this->id = $id->value;
        $this->userId = $userId->value;
        $this->hash = $hash;

        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UserTokenId
    {
        return new UserTokenId($this->id);
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }

    /**
     * @return non-empty-string
     */
    public function getHash(): string
    {
        /**
         * @var non-empty-string $hash
         */
        $hash = $this->hash;

        return $hash;
    }
}
