<?php

declare(strict_types=1);

namespace App\Task;

use App\Task\Http\Export\Csv\CsvExporter;
use App\Task\Http\Export\Exporter;
use App\Task\Http\Export\ExportTaskAction;
use App\Task\Http\Export\ExportTasks;
use App\Task\Http\Export\Xml\XmlExporter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->instanceof(Exporter::class)->tag('app.task.exporter');

    $services->set(CsvExporter::class);
    $services->set(XmlExporter::class);

    $services->set(ExportTasks::class)->arg('$exporters', tagged_iterator('app.task.exporter'));

    $services->set(ExportTaskAction::class)->tag('controller.service_arguments');
};
