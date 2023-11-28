<?php

declare(strict_types=1);

namespace App\Article\Http\ArticleList;

use App\Article\Domain\Articles;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationRequest;
use App\Infrastructure\Response\Pagination\PaginationRequestArgumentResolver;
use App\Infrastructure\Response\Pagination\PaginationResponse;
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
    public function __construct(private Articles $articles) {}

    public function __invoke(
        #[ValueResolver(PaginationRequestArgumentResolver::class)]
        PaginationRequest $paginationRequest,
    ): ApiListObjectResponse {
        $articles = $this->articles->getAll(
            limit: $paginationRequest->limit,
            offset: $paginationRequest->offset,
        );
        $articlesCount = $this->articles->countAll();

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
