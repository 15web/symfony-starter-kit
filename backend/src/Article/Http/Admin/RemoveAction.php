<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

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
