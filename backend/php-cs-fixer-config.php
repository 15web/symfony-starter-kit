<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/src-dev',
        __DIR__.'/tests',
    ])
    ->append([
        __FILE__,
    ]);

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__.'/var/cache/.php-cs-fixer-cache')
    ->setRiskyAllowed(true)
    ->registerCustomFixers([
        new \Dev\PHPCsFixer\Comment\ClassDocCommentFixer(),
        new \Dev\PHPCsFixer\PhpUnit\TestdoxFixer(),
    ])
    ->setRules([
        '@PER' => true,
        '@PER:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP82Migration' => true,
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

        'global_namespace_import' => ['import_classes' => true],

        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_line_span' => true,
        'phpdoc_summary' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],

        'php_unit_test_class_requires_covers' => false,

        'ClassDocComment/class_doc_comment' => true,
        'Testdox/test_requires_testdox' => ['exclude' => 'SDK'],
        'declare_strict_types' => true,
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);
