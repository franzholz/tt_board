<?php


// ******************************************************************
// This is the standard TypoScript Board table, tt_board
// ******************************************************************
$result = array(
	'ctrl' => array (
		'label' => 'subject',
		'default_sortby' => 'ORDER BY parent,crdate DESC',		// crdate should gradually not be used! Trying to phase it out in favour of datetime.
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'copyAfterDuplFields' => 'parent',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'enablecolumns' => array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:' . TT_BOARD_EXT . '/locallang_tca.php:tt_board',
		'typeicon_column' => 'parent',
		'typeicons' => array (
			'0' => 'tt_faq_board_root.gif'
		),
		'useColumnsForDefaultValues' => 'parent',
		'iconfile' => PATH_BE_TTBOARD_REL . 'ext_icon.gif',
		'searchFields' => 'uid,author,email,subject,message,cr_ip',
	),
	'interface' => array (
		'showRecordFieldList' => 'subject,author,email,message'
	),
	'columns' => array (
		'subject' => array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.subject',
			'config' => array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		),
		'message' => array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.message',
			'config' => array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '5'
			)
		),
		'author' => array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
			'config' => array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'email' => array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'hidden' => array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array (
				'type' => 'check'
			)
		),
		'parent' => array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.parent',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'tt_board',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'notify_me' => array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.notify_me',
			'config' => array (
				'type' => 'check'
			)
		),
		'crdate' => array (		// This field is by default filled with creation date. See tt_board 'ctrl' section
			'exclude' => 1,
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.crdate',
			'config' => array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime'
			)
		),
		'cr_ip' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.cr_ip',
			'config' => array (
				'type' => 'input',
				'size' => '15',
				'max' => '15',
			)
		),
		'reference' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.reference',
			'config' => array (
				'type' => 'input',
				'size' => '40',
				'max' => '40',
			)
		)
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;;;1-1-1, crdate, subject;;;;3-3-3, message, author, email, parent;;;;5-5-5, notify_me, cr_ip, reference')
	)
);

return $result;

