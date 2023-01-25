<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Query\PaginationArticles\PaginationArticles;
use App\Article\Query\PaginationArticles\PaginationArticlesQuery;
use App\Article\Query\PaginationArticles\PaginationInfo;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article/pagination-list', methods: ['GET'])]
#[AsController]
final class PaginationAction
{
    public function __construct(private readonly PaginationArticles $paginationArticles)
    {
    }

    public function __invoke(PaginationArticlesQuery $query): PaginationInfo
    {
        return ($this->paginationArticles)($query);
    }
}
