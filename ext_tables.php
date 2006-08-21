<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile(TT_BOARD_EXTkey, 'static/old_style/', 'Board Old Style');
t3lib_extMgm::addStaticFile(TT_BOARD_EXTkey, 'static/css_style/', 'Board CSS Style');

$TCA['tt_board'] = Array (
	'ctrl' => Array (
		'label' => 'subject',
		'default_sortby' => 'ORDER BY parent,crdate DESC',		// crdate should gradually not be used! Trying to phase it out in favour of datetime.
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'copyAfterDuplFields' => 'parent',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'enablecolumns' => Array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:'.TT_BOARD_EXTkey.'/locallang_tca.php:tt_board',
		'typeicon_column' => 'parent',
		'typeicons' => Array (
			'0' => 'tt_faq_board_root.gif'
		),
		'useColumnsForDefaultValues' => 'parent',
		'iconfile' => PATH_BE_ttboard_rel.'ext_icon.gif',
		'dynamicConfigFile' => PATH_BE_ttboard.'tca.php'
	)
);

t3lib_extMgm::addPlugin(Array('LLL:EXT:'.TT_BOARD_EXTkey.'/locallang_tca.php:pi_list', '4'),'list_type');
t3lib_extMgm::addPlugin(Array('LLL:EXT:'.TT_BOARD_EXTkey.'/locallang_tca.php:pi_tree', '2'),'list_type');

t3lib_extMgm::allowTableOnStandardPages('tt_board');
t3lib_extMgm::addToInsertRecords('tt_board');

t3lib_extMgm::addLLrefForTCAdescr('tt_board','EXT:tt_board/locallang_csh_ttboard.php');

if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_ttboard_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_ttboard_wizicon.php';
?>