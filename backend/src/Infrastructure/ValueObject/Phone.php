<?php

declare(strict_types=1);

namespace App\Infrastructure\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * Телефон
 */
#[ORM\Embeddable]
final class Phone
{
    private const string PHONE_NUMBER_REGEX = '/^\d{11}+$/';

    #[ORM\Column]
    private readonly string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(string $value)
    {
        Assert::regex($value, self::PHONE_NUMBER_REGEX, 'Укажите телефон в правильном формате');

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
