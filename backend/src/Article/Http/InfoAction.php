<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Domain\Articles;
use App\Infrastructure\ApiException\ApiNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка информации статьи
 */
#[Route('/articles/{alias}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class InfoAction
{
    public function __construct(private Articles $articles) {}

    public function __invoke(
        #[ValueResolver(RequestAttributeValueResolver::class)]
        string $alias
    ): InfoData {
        $article = $this->articles->findByAlias($alias);
        if ($article === null) {
            throw new ApiNotFoundException('Статья не найдена');
        }

        return new InfoData(
            title: $article->getTitle(),
            body: $article->getBody(),
        );
    }
}
