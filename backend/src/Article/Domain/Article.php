<?php

declare(strict_types=1);

namespace App\Article\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Webmozart\Assert\Assert;

/**
 * Статья
 */
#[ORM\Entity]
/** @final */
class Article
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column]
    private string $title;

    #[ORM\Column]
    private string $alias;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    public function __construct(string $title, string $alias, string $body = '')
    {
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::notEmpty($alias, 'Укажите алиас');

        $this->id = new UuidV7();
        $this->title = $title;
        $this->alias = $alias;
        $this->body = $body;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function change(string $title, string $alias, string $body = ''): void
    {
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::notEmpty($alias, 'Укажите алиас');

        $this->title = $title;
        $this->alias = $alias;
        $this->body = $body;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getBody(): string
    {
        return $this->body;
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
