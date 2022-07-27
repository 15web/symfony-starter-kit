<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())->in([__DIR__.'/src']);

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__.'/var/cache/.php-cs-fixer-cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        // Binary operators should be surrounded by space as configured.
        'binary_operator_spaces' => true,
        // Class `DateTimeImmutable` should be used instead of `DateTime`.
        'date_time_immutable' => true,
        // All classes must be final, except abstract ones and Doctrine entities.
        'final_class' => true,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        // All `public` methods of `abstract` classes should be `final`.
        'final_public_method_for_abstract_class' => true,
        // Forbid multi-line whitespace before the closing semicolon or move the semicolon to the new line for chained calls.
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        // Adds or removes `?` before type declarations for parameters with a default `null` value.
        'nullable_type_declaration_for_default_null_value' => true,
        // Ordering `use` statements.
        'ordered_imports' => true,
        // All items of the given phpdoc tags must be either left-aligned or (by default) aligned vertically.
        'phpdoc_align' => ['align' => 'left'],
        // Changes doc blocks from single to multi line, or reversed. Works for class constants, properties and methods only.
        'phpdoc_line_span' => true,
        // PHPDoc summary should end in either a full stop, exclamation mark, or question mark.
        'phpdoc_summary' => false,
        // Sorts PHPDoc types.
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        // Inside a `final` class or anonymous class `self` should be preferred to `static`.
        'self_static_accessor' => true,
        // Lambdas not (indirect) referencing `$this` must be declared `static`.
        'static_lambda' => true,
        // Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'return_assignment' => false,
    ])
    ->setFinder($finder);
