<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
/** @final */
class TaskComment
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'comments'), ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private readonly Task $task;

    #[ORM\Embedded]
    private TaskCommentBody $body;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(Task $task, TaskCommentId $commentId, TaskCommentBody $taskCommentBody)
    {
        $this->id = $commentId->getValue();
        $this->body = $taskCommentBody;
        $this->task = $task;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }
}
