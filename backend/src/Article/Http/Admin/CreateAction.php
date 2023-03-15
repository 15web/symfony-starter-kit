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
 * Ручка создания статьи
 */
#[IsGranted('ROLE_USER')]
#[Route('/admin/articles/create', methods: ['POST'])]
#[AsController]
final readonly class CreateAction
{
    public function __construct(private Articles $articles, private Flush $flush)
    {
    }

    public function __invoke(CreateRequest $createRequest): Article
    {
        $sameArticle = $this->articles->findByAlias($createRequest->alias);
        if ($sameArticle !== null) {
            throw new ApiBadResponseException(
                'Запись с таким алиасом уже существует',
                ApiErrorCode::ArticleAlreadyExist
            );
        }

        $article = new Article($createRequest->title, $createRequest->alias, $createRequest->body);
        $this->articles->add($article);
        ($this->flush)();

        return $article;
    }
}
