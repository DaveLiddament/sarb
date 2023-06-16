<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('/tests/scratchpad')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'strict_comparison' => true,
        'phpdoc_align' => false,
        'phpdoc_to_comment' => false,
        'native_function_invocation' => false,
    ])
    ->setFinder($finder)
;
