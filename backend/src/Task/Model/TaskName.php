<?php

declare(strict_types=1);

namespace App\Task\Model;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class TaskName
{
    #[ORM\Column]
    private readonly string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value, 'Укажите наименование задачи');

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
