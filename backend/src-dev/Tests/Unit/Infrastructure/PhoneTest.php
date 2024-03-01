<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\Infrastructure;

use App\Infrastructure\ValueObject\Phone;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('Тестирование телефона')]
final class PhoneTest extends TestCase
{
    #[TestDox('Телефоны идентичны')]
    public function testEquals(): void
    {
        $profilePhone1 = new Phone('89272222222');
        $profilePhone2 = new Phone('89272222222');

        self::assertTrue($profilePhone1->equalTo($profilePhone2));
    }

    #[DataProvider('incorrectPhones')]
    #[TestDox('Невалидный номер телефона')]
    public function testIncorrectNumber(string $phone): void
    {
        $this->expectException(InvalidArgumentException::class);

        /**
         * @psalm-suppress ArgumentTypeCoercion
         *
         * @phpstan-ignore-next-line
         */
        new Phone($phone);
    }

    public static function incorrectPhones(): Iterator
    {
        yield 'Неверный формат' => ['неправильный телефон'];

        yield 'Пустой номер' => [''];

        yield '10 цифр' => ['8922222222'];

        yield '12 цифр' => ['892222222222'];
    }
}
