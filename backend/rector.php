<?php

declare(strict_types=1);

use Dev\Rector\OneFlushInClassRector;
use Dev\Rector\RequestMethodInsteadOfStringRector;
use Dev\Rector\ResolversInActionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;

return static function (RectorConfig $rectorConfig): void {
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
        SymfonyLevelSetList::UP_TO_SYMFONY_63,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->skip([
        ClassPropertyAssignToConstructorPromotionRector::class => [__DIR__.'/src/*/Domain/*'],
        InlineConstructorDefaultToPropertyRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        RemoveUnusedPrivatePropertyRector::class => [__DIR__.'/src/*/Domain/*'],
        WebTestCaseAssertIsSuccessfulRector::class,
        WebTestCaseAssertResponseCodeRector::class,
        AddParamTypeSplFixedArrayRector::class => [
            __DIR__ . '/src-dev/PHPCsFixer',
        ],
        __DIR__ . '/src-dev/Maker/Resources/skeleton',
    ]);

    $rectorConfig->rule(RequestMethodInsteadOfStringRector::class);
    $rectorConfig->rule(OneFlushInClassRector::class);
    $rectorConfig->rule(ResolversInActionRector::class);
};
