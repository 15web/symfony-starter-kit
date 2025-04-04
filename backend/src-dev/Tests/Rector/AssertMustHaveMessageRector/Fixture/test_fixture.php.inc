<?php

namespace Dev\Tests\Rector\AssertMustHaveMessageRector\Fixture;

use Webmozart\Assert\Assert;

final readonly class SomeClass
{
    public function handle(mixed $value): void
    {
        Assert::stringNotEmpty($value);
        Assert::greaterThanEq($value, 1);

        // With optional arguments
        Assert::throws($value);
        Assert::throws($value, 'ExceptionClass');
        Assert::resource($value);
        Assert::resource($value, 'ResourceType');
    }
}
?>
-----
<?php

namespace Dev\Tests\Rector\AssertMustHaveMessageRector\Fixture;

use Webmozart\Assert\Assert;

final readonly class SomeClass
{
    public function handle(mixed $value): void
    {
        Assert::stringNotEmpty($value, 'TODO: Указать понятное сообщение ошибки валидации');
        Assert::greaterThanEq($value, 1, 'TODO: Указать понятное сообщение ошибки валидации');

        // With optional arguments
        Assert::throws($value, message: 'TODO: Указать понятное сообщение ошибки валидации');
        Assert::throws($value, 'ExceptionClass', 'TODO: Указать понятное сообщение ошибки валидации');
        Assert::resource($value, message: 'TODO: Указать понятное сообщение ошибки валидации');
        Assert::resource($value, 'ResourceType', 'TODO: Указать понятное сообщение ошибки валидации');
    }
}
?>
