<?php

/**
 * Конфиг PHP-CS-Fixer.
 *
 * Цель: PSR-12 + стандартный набор правил 1С-Битрикс (без жёсткого Symfony)
 * + per-project правила ниже.
 *
 * Запуск:
 *   composer cs-fix      — починить
 *   composer cs-check    — только проверить (CI)
 */

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/mycompany.emptymodule')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                                       => true,
        '@PHP80Migration'                              => true,
        '@PHP81Migration'                              => true,
        'array_syntax'                                 => ['syntax' => 'short'],
        'ordered_imports'                              => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                            => true,
        'no_useless_else'                              => true,
        'no_useless_return'                            => true,
        'single_quote'                                 => true,
        'trailing_comma_in_multiline'                  => ['elements' => ['arrays']],
        'declare_strict_types'                         => true,
        'phpdoc_align'                                 => false,
        'phpdoc_no_alias_tag'                          => false,
        'phpdoc_summary'                               => false,
        'yoda_style'                                   => false,
        'concat_space'                                 => ['spacing' => 'one'],
        'binary_operator_spaces'                       => [
            'default' => 'single_space',
            'operators' => ['=>' => 'aligned'],
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'void_return'                                  => true,
    ])
    ->setLineEnding("\n")
    ->setFinder($finder);
