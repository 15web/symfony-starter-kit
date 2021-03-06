<?php

declare(strict_types=1);

namespace App\Task;

use App\Task\Command\CompleteTask;
use App\Task\Command\CreateTask\CreateTask;
use App\Task\Command\RemoveTask;
use App\Task\Command\UpdateTaskName\UpdateTaskName;
use App\Task\Http\CompleteTaskAction;
use App\Task\Http\CreateTaskAction;
use App\Task\Http\RemoveTaskAction;
use App\Task\Http\TaskArgumentValueResolver;
use App\Task\Http\TaskInfo\TaskInfoAction;
use App\Task\Http\TaskList\TaskListAction;
use App\Task\Http\UpdateTaskNameAction;
use App\Task\Model\Tasks;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(Tasks::class);

    $services->set(CreateTask::class);
    $services->set(UpdateTaskName::class);
    $services->set(CompleteTask::class);
    $services->set(RemoveTask::class);

    $services->set(TaskArgumentValueResolver::class);

    $services->set(TaskInfoAction::class)->tag('controller.service_arguments');
    $services->set(TaskListAction::class)->tag('controller.service_arguments');

    $services->set(CompleteTaskAction::class)->tag('controller.service_arguments');
    $services->set(CreateTaskAction::class)->tag('controller.service_arguments');
    $services->set(RemoveTaskAction::class)->tag('controller.service_arguments');
    $services->set(UpdateTaskNameAction::class)->tag('controller.service_arguments');
};
