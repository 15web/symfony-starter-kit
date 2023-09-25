<?php

declare(strict_types=1);

namespace App\Seo\Http;

final class SeoData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $keywords = null,
    ) {}
}
