<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/admin/article/{id}/info', methods: ['GET'])]
#[AsController]
final class InfoAction
{
    public function __invoke(Article $article): Article
    {
        return $article;
    }
}
