<?php

declare(strict_types=1);

use Dev\PHPCsFixer\Comment\ClassDocCommentFixer;
use Dev\PHPCsFixer\PhpUnit\TestdoxFixer;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = new Finder()
    ->in([
        __DIR__.'/../../src',
        __DIR__.'/../../src-dev',
        __DIR__.'/../../migrations',
    ])
    ->exclude([
        __DIR__.'/../Tests/Rector/*/Fixture/*',
    ])
    ->append([
        __FILE__,
    ]);

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__.'/../../var/cache/.php-cs-fixer')
    ->setRiskyAllowed(true)
    ->registerCustomFixers([
        new ClassDocCommentFixer(),
        new TestdoxFixer(),
    ])
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP8x0Migration:risky' => true,
        '@PHP8x3Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,

        'string_implicit_backslashes' => false,

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

        'php_unit_data_provider_return_type' => false,

        'ClassDocComment/class_doc_comment' => ['exclude' => 'migrations'],
        'Testdox/test_requires_testdox' => ['exclude' => 'SDK'],
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters']],
    ])
    ->setFinder($finder);
