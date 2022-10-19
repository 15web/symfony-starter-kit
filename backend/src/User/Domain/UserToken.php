<?php

declare(strict_types=1);

namespace App\User\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
/** @final */
class UserToken
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'userTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $user;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(Uuid $id, User $user)
    {
        $this->id = $id;
        $this->user = $user;

        $this->createdAt = new \DateTimeImmutable();
    }
}
