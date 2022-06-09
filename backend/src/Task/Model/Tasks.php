<?php

declare(strict_types=1);

namespace App\Task\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

final class Tasks
{
    /**
     * @var EntityRepository<Task>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(Task::class);
    }

    /**
     * @return Task[]
     */
    public function findAll(): array
    {
        return $this->repository->findBy([], ['isCompleted' => 'ASC', 'createdAt' => 'DESC']);
    }

    public function getById(Uuid $id): Task
    {
        $task = $this->repository->find($id);
        if ($task === null) {
            throw new \DomainException('Задача не найдена');
        }

        return $task;
    }

    public function add(Task $task): void
    {
        $this->entityManager->persist($task);
    }

    public function remove(Task $task): void
    {
        $this->entityManager->remove($task);
    }
}
