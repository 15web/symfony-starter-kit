<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\ArticleRepository;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка статей
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/articles', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ArticleListAction
{
    public function __construct(private ArticleRepository $articleRepository) {}

    public function __invoke(): ApiListObjectResponse
    {
        $articles = $this->articleRepository->getAll();
        $articlesCount = $this->articleRepository->countAll();

        $pagination = new PaginationResponse($articlesCount);

        return new ApiListObjectResponse(
            data: $articles,
            pagination: $pagination,
        );
    }
}
