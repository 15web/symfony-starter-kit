<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка удаления статьи
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/articles/{id}', methods: [Request::METHOD_DELETE])]
#[AsController]
final readonly class RemoveAction
{
    public function __construct(private ArticleRepository $articleRepository, private Flush $flush) {}

    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article
    ): ApiObjectResponse {
        $this->articleRepository->remove($article);
        ($this->flush)();

        return new ApiObjectResponse(
            data: new SuccessResponse()
        );
    }
}
