<?php

declare(strict_types=1);

namespace App\Task\Command\Comment\Add;

/**
 * Команда добавления комментария к задаче
 */
final readonly class AddCommentOnTaskCommand
{
    /**
     * @param non-empty-string $commentBody
     */
    public function __construct(public string $commentBody) {}
}
