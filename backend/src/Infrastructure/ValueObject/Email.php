<?php

declare(strict_types=1);

namespace App\Infrastructure\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Webmozart\Assert\Assert;

/**
 * Email
 */
#[Exclude]
#[ORM\Embeddable]
final readonly class Email
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        #[ORM\Column]
        public string $value,
    ) {
        Assert::email($value, 'value: значение должно быть валидным email, указано %s');
    }
}
