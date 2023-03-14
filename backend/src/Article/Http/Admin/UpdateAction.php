<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Flush;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка обновления статьи
 */
#[IsGranted('ROLE_USER')]
#[Route('/admin/articles/{id}/update', methods: ['POST'])]
#[AsController]
final readonly class UpdateAction
{
    public function __construct(private Articles $articles, private Flush $flush)
    {
    }

    public function __invoke(Article $article, UpdateRequest $updateRequest): Article
    {
        $sameArticle = $this->articles->findByAlias($updateRequest->alias);
        if ($sameArticle !== null && $sameArticle->getId() !== $article->getId()) {
            throw new ApiBadResponseException('Запись с таким алиасом уже существует', ApiErrorCode::ArticleAlreadyExist);
        }

        $article->change($updateRequest->title, $updateRequest->alias, $updateRequest->body);

        ($this->flush)();

        return $article;
    }
}
