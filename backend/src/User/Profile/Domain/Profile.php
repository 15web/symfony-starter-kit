<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use App\Infrastructure\Phone;
use App\User\SignUp\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Профиль пользователя
 */
#[ORM\Entity]
#[ORM\Index(fields: ['userId'], name: 'user_id_idx')]
/** @final */
class Profile
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Embedded]
    private Phone $phone;

    #[ORM\Column]
    private string $name;

    public function __construct(
        ProfileId $profileId,
        UserId $userId,
        Phone $phone,
        string $name
    ) {
        $this->id = $profileId->value;
        $this->userId = $userId->value;
        $this->phone = $phone;
        $this->name = $name;
    }

    public function changePhone(Phone $phone): void
    {
        $this->phone = $phone;
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
    }

    public function getProfileId(): ProfileId
    {
        return new ProfileId($this->id);
    }
}
