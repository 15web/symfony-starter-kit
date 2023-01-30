<?php

declare(strict_types=1);

namespace App\Task\Domain;

/**
 * Нельзя добавить комментарий в завершенную задачу
 */
final class AddCommentToCompletedTaskException extends \Exception
{
}
