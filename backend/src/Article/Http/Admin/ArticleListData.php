<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Данные статьи для списка
 */
final readonly class ArticleListData
{
    public function __construct(
        public Uuid $id,
        public string $title,
        public string $alias,
        public DateTimeImmutable $createdAt,
    ) {}
}
