<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Article\Domain\Articles;
use App\Infrastructure\Pagination\PaginationRequest;
use App\Infrastructure\Pagination\PaginationResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка списка статей
 */
#[Route('/articles', methods: ['GET'])]
#[AsController]
final readonly class ArticleListAction
{
    public function __construct(private Articles $articles)
    {
    }

    public function __invoke(PaginationRequest $paginationRequest): ArticleListResponse
    {
        $articles = $this->articles->getAll($paginationRequest->perPage, $paginationRequest->getOffset());
        $articlesCount = $this->articles->countAll();

        $data = [];
        foreach ($articles as $article) {
            $data[] = new ArticleListData($article->getTitle(), $article->getAlias(), $article->getBody());
        }

        $pagination = new PaginationResponse(
            total: $articlesCount,
            perPage: $paginationRequest->perPage,
            currentPage: $paginationRequest->page
        );

        return new ArticleListResponse($data, $pagination);
    }
}
