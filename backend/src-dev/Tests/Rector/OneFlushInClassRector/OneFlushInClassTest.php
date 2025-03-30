<?php

declare(strict_types=1);

namespace Dev\Tests\Rector\OneFlushInClassRector;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @internal
 */
#[TestDox('Проверка вызова flush')]
final class OneFlushInClassTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    #[TestDox('Проверка правила')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture');
    }

    #[Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/config.php';
    }
}
