<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Doctrine\ORM\Mapping as ORM;

/**
 * Наименование задачи
 */
#[ORM\Embeddable]
final readonly class TaskName
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
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value === $other->value;
    }
}
