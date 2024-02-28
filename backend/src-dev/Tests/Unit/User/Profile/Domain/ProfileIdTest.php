<?php

declare(strict_types=1);

namespace Dev\Tests\Unit\User\Profile\Domain;

use App\User\Profile\Domain\ProfileId;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[TestDox('ID профиля')]
final class ProfileIdTest extends TestCase
{
    #[TestDox('Идентификаторы идентичны')]
    public function testEquals(): void
    {
        $profileId1 = new ProfileId();
        $profileId2 = new ProfileId();

        self::assertFalse($profileId1->equalTo($profileId2));
    }
}
