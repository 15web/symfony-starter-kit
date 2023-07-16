<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Article\Domain\Articles;
use App\Infrastructure\Pagination\PaginationRequest;
use App\Infrastructure\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Pagination\PaginationResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка списка статей
 */
#[Route('/articles', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ArticleListAction
{
    public function __construct(private Articles $articles)
    {
    }

    public function __invoke(
        #[ValueResolver(PaginationRequestArgumentResolver::class)]
        PaginationRequest $paginationRequest,
    ): ArticleListResponse {
        $articles = $this->articles->getAll($paginationRequest->limit, $paginationRequest->offset);
        $articlesCount = $this->articles->countAll();

        $data = [];
        foreach ($articles as $article) {
            $data[] = new ArticleListData($article->getTitle(), $article->getAlias(), $article->getBody());
        }

        $pagination = new PaginationResponse(
            total: $articlesCount,
        );

        return new ArticleListResponse($data, $pagination);
    }
}
