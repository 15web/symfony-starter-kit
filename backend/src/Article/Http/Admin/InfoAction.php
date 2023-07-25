<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка информации статьи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/articles/{id}', methods: [Request::METHOD_GET])]
#[AsController]
final class InfoAction
{
    public function __invoke(
        #[ValueResolver(ArticleArgumentValueResolver::class)]
        Article $article
    ): Article {
        return $article;
    }
}
