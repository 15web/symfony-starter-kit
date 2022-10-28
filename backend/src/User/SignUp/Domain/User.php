<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Entity]
/** @final */
class User
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    /**
     * Захешированный пароль
     */
    #[ORM\Embedded]
    private UserPassword $userPassword;

    #[ORM\Embedded]
    private UserEmail $userEmail;

    #[ORM\Column]
    private UserRole $userRole;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(UserId $userId, UserEmail $userEmail, UserRole $userRole, UserPassword $userPassword)
    {
        $this->id = $userId->value;
        $this->userEmail = $userEmail;
        $this->userRole = $userRole;
        $this->userPassword = $userPassword;

        $this->createdAt = new \DateTimeImmutable();
    }

    public function applyPassword(UserPassword $userPassword): void
    {
        Assert::false($this->userPassword->equalTo($userPassword));

        $this->userPassword = $userPassword;
    }
}
