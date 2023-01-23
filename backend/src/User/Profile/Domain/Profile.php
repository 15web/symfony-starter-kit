<?php

declare(strict_types=1);

namespace App\User\Profile\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Profile
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column]
    private readonly string $name;

    #[ORM\Column(nullable: true)]
    private readonly string $phone;

    public function __construct(string $name, string $phone = '')
    {
        Assert::notEmpty($name, 'Укажите Имя');

        $this->id = Uuid::v4();
        $this->name = $name;
        $this->phone = $phone;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
