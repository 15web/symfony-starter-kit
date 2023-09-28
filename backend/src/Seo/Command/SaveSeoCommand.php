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
    /**
     * @param non-empty-string $type
     * @param non-empty-string $identity
     * @param non-empty-string $title
     * @param non-empty-string|null $description
     * @param non-empty-string|null $keywords
     */
    public function __construct(
        public string $type,
        public string $identity,
        public string $title,
        public ?string $description,
        public ?string $keywords,
    ) {
        Assert::inArray($type, array_column(SeoResourceType::cases(), 'value'), 'Указан неверный тип');
    }
}
