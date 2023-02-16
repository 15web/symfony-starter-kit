<?php

declare(strict_types=1);

namespace App\Task\Domain;

use App\User\SignUp\Domain\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Задача
 */
#[ORM\Entity]
#[ORM\Index(fields: ['userId'], name: 'user_id_idx')]
/** @final */
class Task
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Embedded]
    private TaskName $taskName;

    #[ORM\Column]
    private bool $isCompleted;

    /**
     * @var Collection<int, TaskComment>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskComment::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(TaskId $taskId, TaskName $taskName, UserId $userId)
    {
        $this->id = $taskId->value;
        $this->taskName = $taskName;
        $this->userId = $userId->value;
        $this->createdAt = new \DateTimeImmutable();
        $this->completedAt = null;
        $this->updatedAt = null;
        $this->isCompleted = false;
        $this->comments = new ArrayCollection();
    }

    public function changeTaskName(TaskName $taskName): void
    {
        $this->taskName = $taskName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsDone(): void
    {
        if ($this->isCompleted) {
            throw new TaskAlreadyIsDoneException('Задача уже выполнена');
        }

        $this->isCompleted = true;
        $this->updatedAt = new \DateTimeImmutable();
        $this->completedAt = new \DateTimeImmutable();
    }

    public function addComment(TaskComment $taskComment): void
    {
        if ($this->isCompleted) {
            throw new AddCommentToCompletedTaskException('Нельзя добавлять комментарии в выполненную задачу');
        }

        $this->comments->add($taskComment);
    }

    public function getTaskId(): TaskId
    {
        return new TaskId($this->id);
    }
}
