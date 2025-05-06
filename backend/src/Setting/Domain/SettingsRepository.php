<?php

declare(strict_types=1);

namespace App\Setting\Domain;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий Setting
 */
final readonly class SettingsRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function add(Setting $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function findByType(SettingType $type): ?Setting
    {
        return $this->entityManager
            ->getRepository(Setting::class)
            ->findOneBy(['type' => $type->value]);
    }

    /**
     * @return list<Setting>
     */
    public function getAll(): array
    {
        /** @var list<Setting> $settings */
        $settings = $this->entityManager
            ->getRepository(Setting::class)
            ->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $settings;
    }

    /**
     * @return list<Setting>
     */
    public function getAllPublic(): array
    {
        /** @var list<Setting> $settings */
        $settings = $this->entityManager
            ->getRepository(Setting::class)
            ->createQueryBuilder('s')
            ->where('s.isPublic = true')
            ->getQuery()->getResult();

        return $settings;
    }
}
