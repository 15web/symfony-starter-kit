<?php

declare(strict_types=1);

namespace App\Task\Query\Comment\FindAll;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * DTO комментария
 */
final readonly class CommentData
{
    public function __construct(
        public Uuid $id,
        public string $body,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
    ) {
    }
}
