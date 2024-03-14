<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка обновления статьи
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/articles/{id}', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class UpdateAction
{
    public function __construct(private Articles $articles, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article,
        #[ValueResolver(ApiRequestValueResolver::class)]
        UpdateRequest $updateRequest,
    ): ApiObjectResponse {
        $sameArticle = $this->articles->findByAlias($updateRequest->alias);
        if ($sameArticle !== null && $sameArticle->getId() !== $article->getId()) {
            throw new ApiBadResponseException(
                errors: ['Запись с таким алиасом уже существует'],
                apiCode: ApiErrorCode::ArticleAlreadyExist
            );
        }

        $article->change(
            title: $updateRequest->title,
            alias: $updateRequest->alias,
            body: $updateRequest->body,
        );

        ($this->flush)();

        return new ApiObjectResponse(
            data: $article
        );
    }
}
