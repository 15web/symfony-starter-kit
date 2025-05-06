<?php

declare(strict_types=1);

namespace App\Seo\Command;

use App\Infrastructure\Flush;
use App\Seo\Domain\Seo;
use App\Seo\Domain\SeoRepository;

/**
 * Хендлер сохранения SEO
 */
final readonly class SaveSeo
{
    public function __construct(private SeoRepository $seoRepository, private Flush $flush) {}

    public function __invoke(SaveSeoCommand $command): Seo
    {
        $seo = $this->seoRepository->findByTypeIdentity(
            type: $command->type,
            identity: $command->identity,
        );

        if ($seo === null) {
            $seo = new Seo(
                type: $command->type,
                identity: $command->identity,
                title: $command->title,
            );

            $this->seoRepository->add($seo);
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
