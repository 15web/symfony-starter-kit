<?php

declare(strict_types=1);

use Dev\Rector\Rules\OneFlushInClassRector;
use Dev\Rector\Rules\RequestMethodInsteadOfStringRector;
use Dev\Rector\Rules\ResolversInActionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheDirectory(__DIR__.'/../cache/rector');

    $rectorConfig->paths([
        __DIR__.'/../../src',
        __DIR__.'/../',
        __DIR__.'/../../migrations',
        __DIR__.'/../../src-dev/Tests',
    ]);

    $rectorConfig->parallel(processTimeout: 360);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_83,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_64,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->skip([
        ClassPropertyAssignToConstructorPromotionRector::class => [dirname(__DIR__, 2).'/src/*/Domain/*'],
        InlineConstructorDefaultToPropertyRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        RemoveUnusedPrivatePropertyRector::class => [dirname(__DIR__, 2).'/src/*/Domain/*'],
        WebTestCaseAssertIsSuccessfulRector::class,
        WebTestCaseAssertResponseCodeRector::class,
        AddParamTypeSplFixedArrayRector::class => [
            __DIR__.'/../PHPCsFixer',
        ],
        __DIR__.'/../Maker/Resources/skeleton',
        __DIR__.'/../cache',
    ]);

    $rectorConfig->rule(RequestMethodInsteadOfStringRector::class);
    $rectorConfig->rule(OneFlushInClassRector::class);
    $rectorConfig->rule(ResolversInActionRector::class);
};
