<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__.'/var/.php_cs.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'method_chaining_indentation' => true,
        'php_unit_test_case_static_method_calls' => true,
        'static_lambda' => true,
        'ordered_class_elements' => true,
    ])
    ->setFinder($finder)
;
