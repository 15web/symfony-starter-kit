<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Domain\Articles;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article/list', methods: ['GET'])]
#[AsController]
final class ListAction
{
    public function __construct(private readonly Articles $articles)
    {
    }

    /**
     * @return iterable<ListData>
     */
    public function __invoke(): iterable
    {
        $articles = $this->articles->getAll();
        foreach ($articles as $article) {
            yield new ListData($article->getTitle(), $article->getAlias(), $article->getBody());
        }
    }
}
