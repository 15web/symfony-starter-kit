<?php

declare(strict_types=1);

namespace App\Setting\Domain;

/**
 * Типы настроек
 */
enum SettingType: string
{
    /**
     * Наименование сайта
     */
    case SITE_NAME = 'site_name';

    /**
     * Телефон
     */
    case PHONE = 'phone';

    /**
     * Электронная почта сайта
     */
    case EMAIL_SITE = 'email_site';

    /**
     * Электронная почта отправителя
     */
    case EMAIL_FROM = 'email_from';
}
