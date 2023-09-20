<?php

declare(strict_types=1);

namespace App\Seo\Domain;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Webmozart\Assert\Assert;

/**
 * SEO
 */
#[ORM\Entity]
/** @final */
class Seo
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $keywords = null;

    #[ORM\Column()]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\Column(unique: true)]
        private readonly SeoResourceType $type,
        #[ORM\Column]
        private string $identity,
        #[ORM\Column]
        private string $title,
    ) {
        Assert::notEmpty($type, 'Укажите тип');
        Assert::notEmpty($identity, 'Укажите идентификатор');
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::inArray($type->value, array_column(SeoResourceType::cases(), 'value'), 'Указан неверный тип');

        $this->id = new UuidV7();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function change(
        string $title,
        ?string $description,
        ?string $keywords
    ): void {
        Assert::notEmpty($title, 'Укажите заголовок');

        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): SeoResourceType
    {
        return $this->type;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
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
