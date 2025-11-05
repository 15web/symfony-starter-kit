<?php

declare(strict_types=1);

namespace Dev\Tests\Rector\ResolversInAction;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @internal
 */
#[TestDox('Проверка, что в ручке указаны резолверы ко всем аргументам')]
final class ResolversInActionTest extends AbstractRectorTestCase
{
    #[DataProvider('provideCases')]
    #[TestDox('Проверка правила')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideCases(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture');
    }

    #[Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/config.php';
    }
}
