<?php

declare(strict_types=1);

use Dev\Rector\Rules\RequestMethodInsteadOfStringRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        RequestMethodInsteadOfStringRector::class,
    ]);
