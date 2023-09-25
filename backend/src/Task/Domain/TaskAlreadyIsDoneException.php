<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Exception;

/**
 * Нельзя завершить уже завершенную задачу
 */
final class TaskAlreadyIsDoneException extends Exception {}
