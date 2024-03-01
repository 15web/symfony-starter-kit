<?php

declare(strict_types=1);

use Dev\PHPCsFixer\Comment\ClassDocCommentFixer;
use Dev\PHPCsFixer\PhpUnit\TestdoxFixer;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in([
        __DIR__.'/../../src',
        __DIR__.'/../../src-dev',
        __DIR__.'/../../src-dev/Tests',
    ])
    ->exclude('cache')
    ->append([
        __FILE__,
    ]);

return (new Config())
    ->setCacheFile(__DIR__.'/../cache/.php-cs-fixer-cache')
    ->setRiskyAllowed(true)
    ->registerCustomFixers([
        new ClassDocCommentFixer(),
        new TestdoxFixer(),
    ])
    ->setRules([
        '@PER' => true,
        '@PER:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP80Migration:risky' => true,
        '@PHP83Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHPUnit100Migration:risky' => true,

        'escape_implicit_backslashes' => ['heredoc_syntax' => false],

        'final_class' => true,
        'final_public_method_for_abstract_class' => true,
        'date_time_immutable' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'self_static_accessor' => true,
        'static_lambda' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'binary_operator_spaces' => true,
        'ordered_imports' => true,
        'return_assignment' => false,
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'psalm-suppress',
                'phpstan-ignore-next-line',
                'var',
            ],
        ],

        'global_namespace_import' => ['import_classes' => true],

        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_line_span' => true,
        'phpdoc_summary' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],

        'php_unit_test_class_requires_covers' => false,

        'ClassDocComment/class_doc_comment' => true,
        'Testdox/test_requires_testdox' => ['exclude' => 'SDK'],
    ])
    ->setFinder($finder);
