<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
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
    public function __construct(private ArticleRepository $articleRepository, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        CreateRequest $createRequest,
    ): ApiObjectResponse {
        $sameArticle = $this->articleRepository->findByAlias($createRequest->alias);
        if ($sameArticle !== null) {
            throw new ApiBadResponseException(
                errors: ['Запись с таким алиасом уже существует'],
                apiCode: ApiErrorCode::ArticleAlreadyExist,
            );
        }

        $article = new Article(
            title: $createRequest->title,
            alias: $createRequest->alias,
            body: $createRequest->body,
        );

        $this->articleRepository->add($article);
        ($this->flush)();

        return new ApiObjectResponse(
            data: $article,
        );
    }
}
