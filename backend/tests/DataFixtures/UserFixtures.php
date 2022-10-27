<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Infrastructure\AsService;
use App\Infrastructure\Security\CreatePasswordHasher;
use App\User\Domain\User;
use App\User\Domain\UserEmail;
use App\User\Domain\UserId;
use App\User\Domain\UserPassword;
use App\User\Domain\UserRole;
use App\User\Domain\UserToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Uid\Uuid;

#[AsService]
#[When('test')]
#[When('dev')]
final class UserFixtures extends Fixture
{
    public const FIST_USER_EMAIL = 'first@test.ru';
    public const FIST_USER_PASSWORD = '123456';
    public const FIST_USER_TOKEN = '49c59130-09ee-47a0-95bd-6a32d75c6366';

    public const SECOND_USER_EMAIL = 'second@test.ru';
    public const SECOND_USER_PASSWORD = '123456';
    public const SECOND_USER_TOKEN = '9bcb24ec-c34c-442e-9584-39ab29ccfab3';

    public function __construct(
        private readonly CreatePasswordHasher $createPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $userEmail = new UserEmail(self::FIST_USER_EMAIL);
        $userRole = UserRole::User;
        $password = new UserPassword(self::FIST_USER_PASSWORD, $this->createPasswordHasher);
        $userId = new UserId();
        $user = new User($userId, $userEmail, $userRole, $password);
        $user->addToken(new UserToken(Uuid::fromString(self::FIST_USER_TOKEN), $user));

        $manager->persist($user);

        $secondUserEmail = new UserEmail(self::SECOND_USER_EMAIL);
        $secondUserRole = UserRole::User;
        $secondUserPassword = new UserPassword(self::SECOND_USER_PASSWORD, $this->createPasswordHasher);
        $secondUserId = new UserId();
        $secondUser = new User($secondUserId, $secondUserEmail, $secondUserRole, $secondUserPassword);
        $secondUser->addToken(new UserToken(Uuid::fromString(self::SECOND_USER_TOKEN), $secondUser));

        $manager->persist($secondUser);
        $manager->flush();
    }
}
