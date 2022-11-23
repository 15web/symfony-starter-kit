<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Domain\Articles;
use App\Infrastructure\ApiException\ApiNotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article/{alias}/info', methods: ['GET'])]
#[AsController]
final class InfoAction
{
    public function __construct(private readonly Articles $articles)
    {
    }

    public function __invoke(string $alias): InfoData
    {
        $article = $this->articles->findByAlias($alias);
        if ($article === null) {
            throw new ApiNotFoundException('Статья не найдена');
        }

        return new InfoData($article->getTitle(), $article->getBody());
    }
}
