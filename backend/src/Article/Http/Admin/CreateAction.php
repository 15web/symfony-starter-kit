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
 * Ручка создания статьи
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/articles', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class CreateAction
{
    public function __construct(private Articles $articles, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        CreateRequest $createRequest,
    ): ApiObjectResponse {
        $sameArticle = $this->articles->findByAlias($createRequest->alias);
        if ($sameArticle !== null) {
            throw new ApiBadResponseException(
                errors: ['Запись с таким алиасом уже существует'],
                apiCode: ApiErrorCode::ArticleAlreadyExist
            );
        }

        $article = new Article(
            title: $createRequest->title,
            alias: $createRequest->alias,
            body: $createRequest->body,
        );

        $this->articles->add($article);
        ($this->flush)();

        return new ApiObjectResponse(
            data: $article
        );
    }
}
