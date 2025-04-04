<?php

declare(strict_types=1);

use Dev\Rector\Rules\AssertMustHaveMessageRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        AssertMustHaveMessageRector::class,
    ]);
