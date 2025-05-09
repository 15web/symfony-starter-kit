<?php

declare(strict_types=1);

namespace App\Seo\Domain;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий Seo
 */
final readonly class SeoRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function add(Seo $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function findByTypeIdentity(SeoResourceType $type, string $identity): ?Seo
    {
        return $this->entityManager->getRepository(Seo::class)->findOneBy([
            'type' => $type->value,
            'identity' => $identity,
        ]);
    }

    public function findOneByTypeAndIdentity(string $type, string $identity): ?Seo
    {
        return $this->entityManager->getRepository(Seo::class)->findOneBy(['type' => $type, 'identity' => $identity]);
    }
}
