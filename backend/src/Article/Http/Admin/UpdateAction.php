<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка обновления статьи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/articles/{id}/update', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class UpdateAction
{
    public function __construct(private Articles $articles, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article,
        #[ValueResolver(ApiRequestValueResolver::class)]
        UpdateRequest $updateRequest,
    ): Article {
        $sameArticle = $this->articles->findByAlias($updateRequest->alias);
        if ($sameArticle !== null && $sameArticle->getId() !== $article->getId()) {
            throw new ApiBadResponseException(
                errors: ['article.not_found_by_alias'],
                apiCode: ApiErrorCode::ArticleAlreadyExist
            );
        }

        $article->change(
            title: $updateRequest->title,
            alias: $updateRequest->alias,
            body: $updateRequest->body,
        );

        ($this->flush)();

        return $article;
    }
}
