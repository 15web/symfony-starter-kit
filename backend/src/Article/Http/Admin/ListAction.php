<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Articles;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationResponse;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка статей
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/articles', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ListAction
{
    public function __construct(private Articles $articles) {}

    public function __invoke(): ApiListObjectResponse
    {
        $articles = $this->articles->getAll();
        $articlesCount = $this->articles->countAll();

        $pagination = new PaginationResponse($articlesCount);

        return new ApiListObjectResponse(
            data: $articles,
            pagination: $pagination,
        );
    }
}
