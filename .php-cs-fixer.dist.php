<?php

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/var/.php_cs.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]));
