<?php

namespace Dev\Tests\Rector\AssertMustHaveMessageRector\Fixture;

use Webmozart\Assert\Assert;

final readonly class SomeClass
{
    public function handle(mixed $value): void
    {
        Assert::stringNotEmpty($value, 'Value should not be empty.');
        Assert::throws($value, message: 'Expected to throw "%s", got "%s"');
        Assert::throws($value, 'ExceptionClass', 'Expected to throw "%s", got "%s"');
    }
}
?>