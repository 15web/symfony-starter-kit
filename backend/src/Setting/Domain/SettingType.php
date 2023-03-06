<?php

declare(strict_types=1);

namespace App\Setting\Domain;

/**
 * Типы настроек
 */
enum SettingType: string
{
    case SITE_NAME = 'site_name';

    case PHONE = 'phone';

    case EMAIL_SITE = 'email_site';

    case EMAIL_FROM = 'email_from';
}
