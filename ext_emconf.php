<?php

########################################################################
# Extension Manager/Repository config file for ext "tt_board".
########################################################################

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Message board, twin mode',
    'description' => 'Simple threaded (tree) or list message board (forum).',
    'category' => 'plugin',
    'version' => '1.10.15',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'author_company' => 'jambage.com',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.3.99',
            'typo3' => '7.6.0-9.5.99',
            'div2007' => '1.10.15-0.0.0',
            'tslib_fetce' => '0.4.5-0.9.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
            'typo3db_legacy' => '1.0.0-1.1.99',
        ),
    ),
);
