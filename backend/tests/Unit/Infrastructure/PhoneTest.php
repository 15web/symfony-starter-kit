<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\Phone;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox Тестирование телефона
 */
final class PhoneTest extends TestCase
{
    /**
     * @testdox Телефоны идентичны
     */
    public function testEquals(): void
    {
        $profilePhone1 = new Phone('89272222222');
        $profilePhone2 = new Phone('89272222222');

        self::assertTrue($profilePhone1->equalTo($profilePhone2));
    }

    /**
     * @dataProvider incorrectPhones
     *
     * @testdox Невалидный номер телефона
     */
    public function testIncorrectNumber(string $phone): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Phone($phone);
    }

    public function incorrectPhones(): \Iterator
    {
        yield 'Неверный формат' => ['неправильный телефон'];

        yield 'Пустой номер' => [''];

        yield '10 цифр' => ['8922222222'];

        yield '12 цифр' => ['892222222222'];
    }
}
