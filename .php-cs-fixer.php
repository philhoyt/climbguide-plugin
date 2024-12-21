<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('node_modules')
    ->exclude('build')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true,
    'ordered_imports' => true,
    'indentation_type' => true
])
->setIndent("\t")
->setFinder($finder);
