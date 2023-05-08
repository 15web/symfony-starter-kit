<?php

declare(strict_types=1);

namespace App\Seo\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий Seo
 */
#[AsService]
final readonly class SeoCollection
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Seo $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function findByTypeIdentity(string $type, string $identity): ?Seo
    {
        return $this->entityManager->getRepository(Seo::class)->findOneBy([
            'type' => $type,
            'identity' => $identity,
        ]);
    }

    public function findOneByTypeAndIdentity(string $type, string $identity): ?Seo
    {
        return $this->entityManager->getRepository(Seo::class)->findOneBy(['type' => $type, 'identity' => $identity]);
    }
}
