<?php
defined('TYPO3_MODE') || die('Access denied.');

$table = 'tt_board';

// ******************************************************************
// This is the standard Board table, tt_board
// ******************************************************************
$result = [
    'ctrl' => [
        'label' => 'subject',
            'default_sortby' => 'ORDER BY parent,crdate DESC',		// crdate should gradually not be used! Trying to phase it out in favour of datetime.
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
        'searchFields' => 'uid,author,email,subject,message,cr_ip',
    ],
    'interface' => [
        'showRecordFieldList' => 'subject,author,city,email,message'
    ],
    'columns' => [
        'subject' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.subject',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '256',
                'eval' => 'null',
                'default' => NULL,
            ]
        ],
        'message' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.message',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'eval' => 'null',
                'default' => NULL,
            ]
        ],
        'author' => [
            'label' => DIV2007_LANGUAGE_LGL . 'author',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'max' => '80'
            ]
        ],
        'email' => [
            'label'  => DIV2007_LANGUAGE_LGL . 'email',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'max' => '80'
            ]
        ],
        'hidden' => [
            'label'  => DIV2007_LANGUAGE_LGL . 'hidden',
            'config' => [
                'type' => 'check'
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
                'show_thumbs' => '1'
            ]
        ],
        'notify_me' => [
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.notify_me',
            'config' => [
                'type' => 'check'
            ]
        ],
        'crdate' => [		// This field is by default filled with creation date. See tt_board 'ctrl' section
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'max' => '20',
                'eval' => 'datetime'
            ]
        ],
        'cr_ip' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:' . $table . '.cr_ip',
            'config' => [
                'type' => 'input',
                'size' => '15',
                'max' => '15',
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
        '0' => ['showitem' => 'hidden;;;;1-1-1, crdate, subject;;;;3-3-3, message, author, email, parent;;;;5-5-5, notify_me, cr_ip, reference']
    ]
];

return $result;

