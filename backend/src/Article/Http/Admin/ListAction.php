<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
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
    public function __construct(private Articles $articles)
    {
    }

    /**
     * @return Article[]
     */
    public function __invoke(): array
    {
        return $this->articles->getAll();
    }
}
