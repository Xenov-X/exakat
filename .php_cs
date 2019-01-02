<?php

$finder = PhpCsFixer\Finder::create()
    ->in('./library/Exakat/Analyzer')
    ->in('./library/Exakat/Tasks')
    ->in('./library/Exakat/Reports')
    ->in('./library/Exakat/Data')
    ->in('./library/Exakat/Vcs')
    ->in('./library/Exakat/Loader')
    ->in('./library/Exakat/Graph')
    ->in('./library/Exakat/Query')
    ->name('*.php');

return PhpCsFixer\Config::create()
    ->setRules(        
        array(
        'encoding' => true,
        'line_ending' => true,
        'elseif' => true,
        'no_trailing_whitespace' => true,
        'indentation_type' => true
        )
    )
    ->setFinder($finder)
;
