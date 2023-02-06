<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Profile\Domain;

use App\User\Profile\Domain\ProfileId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @testdox ID профиля
 */
final class ProfileIdTest extends TestCase
{
    /**
     * @testdox Идентификаторы идентичны
     */
    public function testEquals(): void
    {
        $profileId1 = new ProfileId();
        $profileId2 = new ProfileId();

        self::assertFalse($profileId1->equalTo($profileId2));
    }
}
