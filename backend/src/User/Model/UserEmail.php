<?php

declare(strict_types=1);

namespace App\User\Model;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class UserEmail
{
    #[ORM\Column]
    private readonly string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);
        Assert::email($value);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
