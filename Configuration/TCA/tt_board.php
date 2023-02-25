<?php
defined('TYPO3') || die('Access denied.');

$table = 'tt_board';

// ******************************************************************
// This is the standard Board table, tt_board
// ******************************************************************
$result = [
    'ctrl' => [
        'label' => 'subject',
        'default_sortby' => 'ORDER BY parent,crdate DESC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'copyAfterDuplFields' => 'parent',
        'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'title' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:tt_board',
        'typeicon_column' => 'parent',
        'typeicons' => [
            '0' => 'tt_faq_board_root.gif'
        ],
        'useColumnsForDefaultValues' => 'parent',
        'iconfile' => 'EXT:' . TT_BOARD_EXT . '/ext_icon.gif',
        'searchFields' => 'uid,author,city,email,subject,message,cr_ip',
    ],
    'columns' => [
        'subject' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.subject',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '256',
                'eval' => 'null',
                'default' => null,
            ]
        ],
        'message' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.message',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'eval' => 'null',
                'default' => null
            ]
        ],
        'author' => [
            'label' => DIV2007_LANGUAGE_LGL . 'author',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'max' => '80',
                'default' => ''
            ]
        ],
        'city' => [
            'label'  => DIV2007_LANGUAGE_LGL . 'city',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 255,
                'default' => ''
            ]
        ],
        'email' => [
            'label'  => DIV2007_LANGUAGE_LGL . 'email',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'max' => '80',
                'default' => ''
            ]
        ],
        'hidden' => [
            'label'  => DIV2007_LANGUAGE_LGL . 'hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'parent' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.parent',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                    'allowed' => $table,
                'size' => '3',
                'maxitems' => '1',
                'minitems' => '0',
                'show_thumbs' => '1',
                'default' => 0
            ]
        ],
        'notify_me' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.notify_me',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'crdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => '8',
                'eval' => 'date',
                'renderType' => 'inputDateTime',
                'default' => 0
            ]
        ],
        'tstamp' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.tstamp',
            'config' => [
                'type' => 'input',
                'size' => '8',
                'eval' => 'date',
                'renderType' => 'inputDateTime',
                'default' => 0
            ]
        ],
        'cr_ip' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.cr_ip',
            'config' => [
                'type' => 'input',
                'size' => '15',
                'max' => '15',
                'default' => ''
            ]
        ],
        'reference' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.reference',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '40',
                'eval' => 'null',
                'default' => null,
            ]
        ]
    ],
    'types' => [
        '0' => ['showitem' => 'hidden, crdate, tstamp, subject, message, author, city, email, parent, notify_me, cr_ip, reference']
    ]
];

return $result;

