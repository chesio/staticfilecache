<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Static File Cache',
    'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase performance for static pages by a factor of 230!!',
    'category' => 'fe',
    'version' => '6.0.0',
    'state' => 'stable',
    'modify_tables' => 'pages',
    'clearcacheonload' => true,
    'author' => 'Static File Cache Team',
    'author_email' => 'tim@fruit-lab.de',
    'author_company' => 'Static File Cache Team',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.3.99',
            'php' => '7.0.0-0.0.0',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'SFC\\Staticfilecache\\' => 'Classes'
        ],
    ],
];
