<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/packages')
    ->exclude(
        [
            'tmp',
            'resources',
        ]
    )
    ->name('*.php')
    ->notName('*.blade.php');

return new Config()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PER-CS' => true,

            // Project conventions on top of PER Coding Style
            'declare_strict_types' => true,
            'new_with_parentheses' => [
                'anonymous_class' => false,
                'named_class' => false,
            ],
            'nullable_type_declaration_for_default_null_value' => true,
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
            ],
            'no_unused_imports' => true,
            'single_quote' => true,
            'trailing_comma_in_multiline' => false,
            // 'trailing_comma_in_multiline' => [
            //     'elements' => ['arrays', 'arguments', 'parameters'],
            // ],
            'concat_space' => [
                'spacing' => 'one',
            ],
            'array_syntax' => [
                'syntax' => 'short',
            ],
            'phpdoc_align' => [
                'align' => 'vertical',
                'spacing' => 2,
            ],
            'phpdoc_summary' => false,
            'phpdoc_to_comment' => false,
            'yoda_style' => false,
            'increment_style' => false,
            'global_namespace_import' => [
                'import_classes' => null,
                'import_constants' => null,
                'import_functions' => null,
            ],
        ]
    )
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder);


