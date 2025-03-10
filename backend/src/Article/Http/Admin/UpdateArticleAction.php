<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка обновления статьи
 */
#[IsGranted(UserRole::Admin)]
#[Route('/admin/articles/{id}', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class UpdateArticleAction
{
    public function __construct(private ArticleRepository $articleRepository, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article,
        #[ValueResolver(ApiRequestValueResolver::class)]
        UpdateArticleRequest $updateRequest,
    ): ApiObjectResponse {
        $sameArticle = $this->articleRepository->findByAlias($updateRequest->alias);
        if ($sameArticle !== null && $sameArticle->getId() !== $article->getId()) {
            throw new ApiBadResponseException(
                errors: ['Запись с таким алиасом уже существует'],
                apiCode: ApiErrorCode::ArticleAlreadyExist,
            );
        }

        $article->change(
            title: $updateRequest->title,
            alias: $updateRequest->alias,
            body: $updateRequest->body,
        );

        ($this->flush)();

        return new ApiObjectResponse(
            data: $article,
        );
    }
}
