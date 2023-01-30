<?php

declare(strict_types=1);

namespace App\Task\Domain;

/**
 * Нельзя завершить уже завершенную задачу
 */
final class TaskAlreadyIsDoneException extends \Exception
{
}
