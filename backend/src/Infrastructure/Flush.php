<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Флашер
 */
#[AsService]
final class Flush
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(): void
    {
        $this->entityManager->flush();
    }
}
