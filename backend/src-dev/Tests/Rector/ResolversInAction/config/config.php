<?php

declare(strict_types=1);

use Dev\Rector\Rules\ResolversInActionRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ResolversInActionRector::class,
    ]);
