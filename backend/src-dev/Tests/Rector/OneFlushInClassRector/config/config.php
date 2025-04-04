<?php

declare(strict_types=1);

use Dev\Rector\Rules\OneFlushInClassRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        OneFlushInClassRector::class,
    ]);
