<?php

declare(strict_types=1);

namespace App\Setting\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Webmozart\Assert\Assert;

/**
 * Настройки
 */
#[ORM\Entity]
/** @final */
class Setting
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(length: 255)]
    private string $value;

    #[ORM\Column(unique: true)]
    private readonly SettingType $type;

    #[ORM\Column]
    private readonly bool $isPublic;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    /**
     * @param non-empty-string $value
     */
    public function __construct(SettingType $type, string $value, bool $isPublic)
    {
        Assert::inArray($type, array_column(SettingType::cases(), 'value'), 'Указан неверный тип');

        $this->id = new UuidV7();
        $this->type = $type;
        $this->value = $value;
        $this->isPublic = $isPublic;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    /**
     * @param non-empty-string $value
     */
    public function change(string $value): void
    {
        $this->value = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function getType(): SettingType
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
