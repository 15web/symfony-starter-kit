<?php

declare(strict_types=1);

namespace App\Seo\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Seo\Domain\Seo;
use App\Seo\Domain\SeoCollection;
use App\Seo\Domain\SeoResourceType;

/**
 * Хендлер сохранения SEO
 */
#[AsService]
final readonly class SaveSeo
{
    public function __construct(private SeoCollection $seoCollection, private Flush $flush)
    {
    }

    public function __invoke(SaveSeoCommand $command): Seo
    {
        $seo = $this->seoCollection->findByTypeIdentity($command->type, $command->identity);

        if ($seo === null) {
            $seo = new Seo(
                type: SeoResourceType::from($command->type),
                identity: $command->identity,
                title: $command->title,
            );

            $this->seoCollection->add($seo);
        }

        $seo->change(
            title: $command->title,
            description: $command->description,
            keywords: $command->keywords,
        );

        ($this->flush)();

        return $seo;
    }
}
