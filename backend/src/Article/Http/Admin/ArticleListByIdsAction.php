<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка получения статей по списку Id
 */
#[IsGranted(UserRole::Admin)]
#[Route('/admin/articles-list', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class ArticleListByIdsAction
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        ArticleListByIdsRequest $request,
    ): ApiListObjectResponse {
        $articles = $this->articleRepository->getByIds(
            ids: $request->ids,
        );

        $pagination = new PaginationResponse(
            total: \count($articles),
        );

        return new ApiListObjectResponse(
            data: $this->buildResponseData(
                ids: $request->ids,
                articles: $articles,
            ),
            pagination: $pagination,
        );
    }

    /**
     * @param non-empty-list<Uuid> $ids
     * @param list<Article> $articles
     *
     * @return iterable<Article>
     */
    private function buildResponseData(array $ids, array $articles): iterable
    {
        $articleCollection = new ArrayCollection($articles);

        foreach ($ids as $id) {
            /** @var Article|null $article */
            $article = $articleCollection->findFirst(
                static fn (int $i, Article $article): bool => $article->getId()->equals($id),
            );

            if ($article === null) {
                continue;
            }

            yield $article;
        }
    }
}
