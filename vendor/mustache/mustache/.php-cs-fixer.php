<?php

use PhpCsFixer\Config;

$config = new Config();

$config->setRules([
    '@Symfony' => true,
    'binary_operator_spaces' => false,
    'concat_space' => ['spacing' => 'one'],
    'increment_style' => false,
    'single_line_throw' => false,
    'yoda_style' => false,
]);

$finder = $config->getFinder()
    ->in('src')
    ->in('test');

return $config;
