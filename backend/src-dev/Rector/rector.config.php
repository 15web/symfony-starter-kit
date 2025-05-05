<?php

declare(strict_types=1);

use Dev\Rector\Rules\AssertMustHaveMessageRector;
use Dev\Rector\Rules\OneFlushInClassRector;
use Dev\Rector\Rules\RequestMethodInsteadOfStringRector;
use Dev\Rector\Rules\ResolversInActionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;

return RectorConfig::configure()
    ->withCache(__DIR__.'/../../var/cache/rector')
    ->withPaths([
        __DIR__.'/../../src',
        __DIR__.'/../',
        __DIR__.'/../../migrations',
        __DIR__.'/../../src-dev/Tests',
    ])
    ->withParallel()
    ->withImportNames(importShortClasses: false)
    ->withPhpSets()
    ->withAttributesSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
    )
    ->withRules([
        RequestMethodInsteadOfStringRector::class,
        OneFlushInClassRector::class,
        ResolversInActionRector::class,
        PreferPHPUnitSelfCallRector::class,
        AssertMustHaveMessageRector::class,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [dirname(__DIR__, 2).'/src/*/Domain/*'],
        InlineConstructorDefaultToPropertyRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PreferPHPUnitThisCallRector::class,
        RemoveUnusedPrivatePropertyRector::class => [dirname(__DIR__, 2).'/src/*/Domain/*'],
        AddParamTypeSplFixedArrayRector::class => [
            __DIR__.'/../PHPCsFixer',
        ],
        __DIR__.'/../Tests/bootstrap.php',
        __DIR__.'/../Tests/Rector/*/Fixture/*',
    ]);
