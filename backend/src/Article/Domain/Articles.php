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
final readonly class Articles
{
    public function __construct(private EntityManagerInterface $entityManager)
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
    public function getAll(int $limit = 10, int $offset = 0): array
    {
        /** @var Article[] $articles */
        $articles = $this->entityManager->getRepository(Article::class)->createQueryBuilder('a')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $articles;
    }

    public function countAll(): int
    {
        /** @var int $countAll */
        $countAll = $this->entityManager->getRepository(Article::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $countAll;
    }
}
