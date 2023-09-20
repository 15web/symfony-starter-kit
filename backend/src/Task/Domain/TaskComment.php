<?php

declare(strict_types=1);

namespace App\Task\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Комментарий задачи
 */
#[ORM\Entity]
/** @final */
class TaskComment
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'comments'),
            ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly Task $task,
        TaskCommentId $commentId,
        #[ORM\Embedded]
        private TaskCommentBody $body
    ) {
        $this->id = $commentId->getValue();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}
