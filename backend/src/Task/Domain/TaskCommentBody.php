<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\Infrastructure\ValueObject\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Override;

/**
 * Текст комментария задачи
 */
#[ORM\Embeddable]
final readonly class TaskCommentBody implements ValueObject
{
    #[ORM\Column]
    private string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param object $other
     */
    #[Override]
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
