<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Response\ApiObjectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка информации статьи
 */
#[Route('/articles/{alias}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class InfoAction
{
    public function __construct(private ArticleRepository $articleRepository) {}

    public function __invoke(
        #[ValueResolver(RequestAttributeValueResolver::class)]
        string $alias,
    ): ApiObjectResponse {
        $article = $this->articleRepository->findByAlias($alias);
        if ($article === null) {
            throw new ApiNotFoundException(['Статья не найдена']);
        }

        return new ApiObjectResponse(
            data: $this->buildResponseData($article),
        );
    }

    private function buildResponseData(Article $article): InfoData
    {
        return new InfoData(
            title: $article->getTitle(),
            body: $article->getBody(),
        );
    }
}
