<?php

declare(strict_types=1);

namespace App\Article\Http\Site;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\Request\Pagination\PaginationRequest;
use App\Infrastructure\Request\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка статей
 */
#[Route('/articles', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ArticleListAction
{
    public function __construct(private ArticleRepository $articleRepository) {}

    public function __invoke(
        #[ValueResolver(PaginationRequestArgumentResolver::class)]
        PaginationRequest $paginationRequest,
    ): ApiListObjectResponse {
        $articles = $this->articleRepository->getAll(
            limit: $paginationRequest->limit,
            offset: $paginationRequest->offset,
        );

        $articlesCount = $this->articleRepository->countAll();

        $pagination = new PaginationResponse($articlesCount);

        return new ApiListObjectResponse(
            data: $this->buildResponseData($articles),
            pagination: $pagination,
        );
    }

    /**
     * @param list<Article> $articles
     *
     * @return iterable<ArticleListData>
     */
    private function buildResponseData(array $articles): iterable
    {
        foreach ($articles as $article) {
            yield new ArticleListData(
                title: $article->getTitle(),
                alias: $article->getAlias(),
                body: $article->getBody(),
            );
        }
    }
}
