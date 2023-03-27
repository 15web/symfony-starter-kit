<?php

declare(strict_types=1);

return [
    'user' => [
        'exception' => [
            'already_exist' => 'Пользователь с таким email уже существует',
            'not_confirmed_email' => 'E-mail пользователя не подтвержден',
            'email_already_is_confirmed' => 'Email уже подтвержден',
        ],
        'mail' => [
            'confirm_email_subject' => 'Подтверждение email',
            'confirm_email_text' => 'Токен подтверждения email',
        ],
    ],
];
