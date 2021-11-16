<?php

########################################################################
# Extension Manager/Repository config file for ext "tt_board".
########################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'Message board, twin mode',
    'description' => 'Simple threaded (tree) or list message board (forum).',
    'category' => 'plugin',
    'version' => '1.11.3',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'author_company' => 'jambage.com',
    'constraints' => [
        'depends' => [
            'php' => '7.3.1-7.4.99',
            'typo3' => '9.5.0-10.4.99',
            'div2007' => '1.12.0-0.0.0',
            'tslib_fetce' => '0.5.4-0.9.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
