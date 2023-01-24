<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/admin/article/{id}/remove', methods: ['POST'])]
#[AsController]
final class RemoveAction
{
    public function __construct(private readonly Articles $articles, private readonly Flush $flush)
    {
    }

    public function __invoke(Article $article): SuccessResponse
    {
        $this->articles->remove($article);
        ($this->flush)();

        return new SuccessResponse();
    }
}
