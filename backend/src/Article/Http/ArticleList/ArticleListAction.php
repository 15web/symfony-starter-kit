<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Article\Domain\ArticleRepository;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationRequest;
use App\Infrastructure\Response\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Response\Pagination\PaginationResponse;
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

        $data = [];
        foreach ($articles as $article) {
            $data[] = new ArticleListData(
                title: $article->getTitle(),
                alias: $article->getAlias(),
                body: $article->getBody(),
            );
        }

        $pagination = new PaginationResponse($articlesCount);

        return new ApiListObjectResponse(
            data: $data,
            pagination: $pagination,
        );
    }
}
