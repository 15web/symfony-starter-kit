<?php

declare(strict_types=1);

use Dev\Rector\OneFlushInClassRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector;
use Rector\Symfony\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->cacheDirectory(__DIR__.'/var/cache/rector');
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/src-dev',
        __DIR__.'/migrations',
        __DIR__.'/tests',
    ]);

    $rectorConfig->parallel(seconds: 360);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_82,
        SymfonyLevelSetList::UP_TO_SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->skip([
        ClassPropertyAssignToConstructorPromotionRector::class => [__DIR__.'/src/*/Domain/*'],
        InlineConstructorDefaultToPropertyRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        RemoveUnusedPrivatePropertyRector::class => [__DIR__.'/src/*/Domain/*'],
        WebTestCaseAssertIsSuccessfulRector::class,
        WebTestCaseAssertResponseCodeRector::class,
    ]);

    $rectorConfig->rule(OneFlushInClassRector::class);
};
