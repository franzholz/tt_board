<?php

########################################################################
# Extension Manager/Repository config file for ext "tt_board".
########################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'Message board, twin mode',
    'description' => 'Simple threaded (tree) or list message board (forum).',
    'category' => 'plugin',
    'version' => '1.13.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'clearcacheonload' => 1,
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'author_company' => 'jambage.com',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
            'div2007' => '1.16.2-0.0.0',
            'tslib_fetce' => '0.6.0-0.10.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
