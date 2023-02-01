<?php

declare(strict_types=1);

namespace App\Article\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий статей
 */
#[AsService]
final class Articles
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function add(Article $article): void
    {
        $this->entityManager->persist($article);
    }

    public function remove(Article $article): void
    {
        $this->entityManager->remove($article);
    }

    public function findById(Uuid $id): ?Article
    {
        return $this->entityManager->getRepository(Article::class)->find($id);
    }

    public function findByAlias(string $alias): ?Article
    {
        return $this->entityManager->getRepository(Article::class)->findOneBy(['alias' => $alias]);
    }

    /**
     * @return Article[]
     */
    public function getAll(): array
    {
        return $this->entityManager->getRepository(Article::class)->findBy([], ['createdAt' => 'DESC']);
    }
}
