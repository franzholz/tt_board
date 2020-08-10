<?php

########################################################################
# Extension Manager/Repository config file for ext "tt_board".
########################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'Message board, twin mode',
    'description' => 'Simple threaded (tree) or list message board (forum).',
    'category' => 'plugin',
    'version' => '1.11.2',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'author_company' => 'jambage.com',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.3.99',
            'typo3' => '8.7.0-9.5.99',
            'div2007' => '1.10.28-0.0.0',
            'tslib_fetce' => '0.5.1-0.9.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
