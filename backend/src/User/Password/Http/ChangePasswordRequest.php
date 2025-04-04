<?php

declare(strict_types=1);

namespace App\User\Password\Http;

use App\User\User\Domain\UserPassword;
use SensitiveParameter;
use Webmozart\Assert\Assert;

/**
 * Запрос на смену текущего пароля
 */
final readonly class ChangePasswordRequest
{
    /**
     * @param non-empty-string $currentPassword Текущий пароль
     * @param non-empty-string $newPassword Новый пароль
     * @param non-empty-string $newPasswordConfirmation Подтверждение нового пароля
     */
    public function __construct(
        #[SensitiveParameter]
        public string $currentPassword,
        #[SensitiveParameter]
        public string $newPassword,
        #[SensitiveParameter]
        public string $newPasswordConfirmation,
    ) {
        Assert::minLength($this->newPassword, UserPassword::MIN_LENGTH, 'newPassword: длина не может быть ментьше %2$s симоволов, указано %s');
        Assert::eq($this->newPassword, $this->newPasswordConfirmation, 'newPasswordConfirmation: пароль и его повтор не совпадают');
        Assert::notEq($this->newPassword, $this->currentPassword, 'newPassword: новый пароль не может совпадать с текущим');
    }
}
