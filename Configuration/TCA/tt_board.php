<?php

defined('TYPO3') || die('Access denied.');

$table = 'tt_board';
$extensionKey = 'tt_board';
$languageSubpath = '/Resources/Private/Language/';

$languageLglPath = 'LLL:EXT:core' . $languageSubpath . 'locallang_general.xlf:LGL.';


// ******************************************************************
// This is the standard Board table tt_board
// ******************************************************************
$result = [
    'ctrl' => [
        'label' => 'subject',
        'default_sortby' => 'ORDER BY parent,crdate DESC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'copyAfterDuplFields' => 'parent',
        'prependAtCopy' => $languageLglPath . 'locallang_general.xlf:LGL.prependAtCopy',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'title' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:tt_board',
        'useColumnsForDefaultValues' => 'parent',
        'iconfile' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/Extension.gif',
        'searchFields' => 'uid,author,city,email,subject,message,cr_ip,slug',
    ],
    'columns' => [
        'subject' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.subject',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '256',
                'nullable' => true,
                'default' => null,
            ]
        ],
        'message' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.message',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'nullable' => true,
                'default' => null
            ]
        ],
        'author' => [
            'label' => $languageLglPath . 'author',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'max' => '80',
                'nullable' => true,
                'default' => null
            ]
        ],
        'city' => [
            'label'  => $languageLglPath . 'city',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'nullable' => true,
                'max' => 255,
                'default' => null
            ]
        ],
        'email' => [
            'label'  => $languageLglPath . 'email',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'eval' => 'trim',
                'nullable' => true,
                'max' => '80',
                'default' => null
            ]
        ],
        'hidden' => [
            'label'  => $languageLglPath . 'hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'parent' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.parent',
            'config' => [
                'type' => 'group',
                'allowed' => $table,
                'size' => '3',
                'maxitems' => '1',
                'minitems' => '0',
                'show_thumbs' => '1',
                'default' => 0
            ]
        ],
        'notify_me' => [
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.notify_me',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'crdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.crdate',
            'config' => [
                'type' => 'datetime',
                'size' => '8',
                'eval' => 'date',
                'default' => 0
            ]
        ],
        'tstamp' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.tstamp',
            'config' => [
                'type' => 'datetime',
                'size' => '8',
                'eval' => 'date',
                'default' => 0
            ]
        ],
        'cr_ip' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.cr_ip',
            'config' => [
                'type' => 'input',
                'size' => '15',
                'max' => '15',
                'nullable' => true,
                'default' => null
            ]
        ],
        'reference' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.reference',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '40',
                'nullable' => true,
                'default' => null,
            ]
        ],
        'slug' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:' . $extensionKey . $languageSubpath . 'locallang_tca.xlf:' . $table . '.slug',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'eval' => 'uniqueInSite',
                'generatorOptions' => [
                    'fields' => [
                        'subject'
                    ],
                    'fieldSeparator' => '_',
                    'replacements' => [
                        '/' => '-',
                        ' ' => '-',
                        '"' => '-',
                        '\'' => '-',
                        '&' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'default' => null,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden, crdate, tstamp, subject, subject_addition, message, author, city, email, parent, notify_me, cr_ip, reference, slug']
    ]
];

return $result;
