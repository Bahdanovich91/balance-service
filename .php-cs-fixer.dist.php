<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/app', __DIR__ . '/tests'])
    ->exclude('var')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_before_statement' => ['statements' => ['yield', 'yield_from', 'throw', 'try', 'return']],
        'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => ['spacing' => 'one'],
        'control_structure_continuation_position' => true,
        'dir_constant' => true,
        'fopen_flag_order' => true,
        'function_declaration' => ['closure_fn_spacing' => 'none'],
        'list_syntax' => ['syntax' => 'short'],
        'logical_operators' => true,
        'mb_str_functions' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'multiline_whitespace_before_semicolons' => true,
        'new_with_braces' => false,
        'no_alias_functions' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra',
                'parenthesis_brace_block', 'square_brace_block', 'switch', 'throw', 'use', 'return',
            ]
        ],
        'no_superfluous_phpdoc_tags' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'operator_linebreak' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public', 'constant_protected', 'constant_private',
                'property_public_static', 'property_protected_static', 'property_private_static',
                'property_public', 'property_protected', 'property_private',
                'method_public_abstract', 'method_protected_abstract',
                'method_public_abstract_static', 'method_protected_abstract_static',
                'construct',
                'method_public', 'method_protected', 'method_private',
                'method_public_static', 'method_protected_static', 'method_private_static',
                'destruct', 'magic', 'phpunit'
            ]
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha'
        ],
        'simplified_if_return' => true,
        'single_trait_insert_per_statement' => false,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => false,
        'use_arrow_functions' => true,
        'yoda_style' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
