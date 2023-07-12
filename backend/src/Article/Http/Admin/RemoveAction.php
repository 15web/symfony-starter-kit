<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка удаления статьи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/articles/{id}/remove', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class RemoveAction
{
    public function __construct(private Articles $articles, private Flush $flush)
    {
    }

    public function __invoke(#[ValueResolver(ArticleArgumentValueResolver::class)] Article $article): SuccessResponse
    {
        $this->articles->remove($article);
        ($this->flush)();

        return new SuccessResponse();
    }
}
