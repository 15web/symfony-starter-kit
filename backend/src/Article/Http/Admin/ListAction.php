<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Articles;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationResponse;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка списка статей
 */
#[IsGranted(UserRole::User->value)]
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
