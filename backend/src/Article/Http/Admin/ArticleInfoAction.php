<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка информации статьи
 */
#[IsGranted(UserRole::Admin)]
#[Route('/admin/articles/{id}', methods: [Request::METHOD_GET])]
#[AsController]
final class ArticleInfoAction
{
    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article,
    ): ApiObjectResponse {
        return new ApiObjectResponse(
            data: $article,
        );
    }
}
