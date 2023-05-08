<?php

declare(strict_types=1);

namespace App\Seo\Domain;

/**
 * Типы ресурсов для SEO
 */
enum SeoResourceType: string
{
    case ARTICLE = 'article';

    case TASK = 'task';
}
