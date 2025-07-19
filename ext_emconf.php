<?php

########################################################################
# Extension Manager/Repository config file for ext "tt_board".
########################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'Message board, twin mode',
    'description' => 'Simple threaded (tree) or list message board (forum).',
    'category' => 'plugin',
    'version' => '1.18.1',
    'state' => 'stable',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'author_company' => 'jambage.com',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'div2007' => '2.3.3-0.0.0',
            'tslib_fetce' => '0.9.1-0.15.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
