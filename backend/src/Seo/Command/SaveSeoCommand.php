<?php

declare(strict_types=1);

namespace App\Seo\Command;

use App\Seo\Domain\SeoResourceType;
use Webmozart\Assert\Assert;

/**
 * Команда сохранения SEO
 */
final readonly class SaveSeoCommand
{
    public function __construct(
        public string $type,
        public string $identity,
        public string $title,
        public ?string $description,
        public ?string $keywords,
    ) {
        Assert::notEmpty($type, 'Укажите тип');
        Assert::notEmpty($identity, 'Укажите идентификатор');
        Assert::notEmpty($title, 'Укажите заголовок');
        Assert::inArray($type, array_column(SeoResourceType::cases(), 'value'), 'Указан неверный тип');
        Assert::nullOrNotEmpty($description, 'Укажите описание');
        Assert::nullOrNotEmpty($keywords, 'Укажите ключевые слова');
    }
}
