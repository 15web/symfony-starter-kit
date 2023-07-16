<?php

declare(strict_types=1);

namespace App\Seo\Http;

use App\Seo\Domain\SeoCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка получения SEO
 */
#[Route('/seo/{type}/{identity}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class SeoAction
{
    public function __construct(private SeoCollection $seoCollection)
    {
    }

    public function __invoke(
        #[ValueResolver(RequestAttributeValueResolver::class)]
        string $type,
        #[ValueResolver(RequestAttributeValueResolver::class)]
        string $identity,
    ): SeoData {
        $seo = $this->seoCollection->findOneByTypeAndIdentity($type, $identity);

        return new SeoData(
            title: $seo?->getTitle(),
            description: $seo?->getDescription(),
            keywords: $seo?->getKeywords(),
        );
    }
}
