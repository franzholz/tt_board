<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$typoVersion =
	class_exists('TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility') ?
		\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) :
		t3lib_div::int_from_ver(TYPO3_version);

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (!defined ('TT_BOARD_EXTkey')) {
	define('TT_BOARD_EXTkey', $_EXTKEY);
}

if (!defined ('PATH_BE_ttboard')) {
	define('PATH_BE_ttboard', t3lib_extMgm::extPath(TT_BOARD_EXTkey));
}

if (!defined ('PATH_BE_ttboard_rel')) {
	define('PATH_BE_ttboard_rel', t3lib_extMgm::extRelPath(TT_BOARD_EXTkey));
}

if (!defined ('PATH_FE_ttboard_rel')) {
	define('PATH_FE_ttboard_rel', t3lib_extMgm::siteRelPath(TT_BOARD_EXTkey));
}

if (!defined ('PATH_ttboard_icon_table_rel')) {
	define('PATH_ttboard_icon_table_rel', PATH_BE_ttboard_rel.'res/icons/table/');
}

if (!defined ('TT_BOARD_DIV_DLOG')) {
	define('TT_BOARD_DIV_DLOG', '0');	// for development error logging
}


if (isset($_EXTCONF) && is_array($_EXTCONF)) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey] = $_EXTCONF;
	if (isset($tmpArray) && is_array($tmpArray)) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey] =
			array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey], $tmpArray);
	}
} else {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey] = array();
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey]['useFlexforms'] = 1;
}


if (TYPO3_MODE == 'BE' && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXTkey]['useFlexforms'] && defined('PATH_BE_div2007')) {
	// replace the output of the former CODE field with the flexform
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][2][] =
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][4][] =
		'EXT:' . TT_BOARD_EXTkey . '/hooks/class.tx_ttboard_hooks_cms.php:&tx_ttboard_hooks_cms->pmDrawItem';
}

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addUserTSConfig('options.saveDocNew.tt_board=1');

	## Extending TypoScript from static template uid=43 to set up userdefined tag:
	t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tt_board_list = < plugin.tt_board_list.CSS_editor ', 43);
	t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tt_board_tree = < plugin.tt_board_tree.CSS_editor ', 43);
}

if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'])) {
	// TYPO3 4.5 with livesearch
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'] = array_merge(
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'],
		array(
			'tt_board' => 'tt_board'
		)
	);
}



// support for new Caching Framework


// Register cache 'tt_board_cache'
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache'] = array();
}
// Define string frontend as default frontend, this must be set with TYPO3 4.5 and below
// and overrides the default variable frontend of 4.6
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['frontend'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['frontend'] = 't3lib_cache_frontend_StringFrontend';
}



if ($typoVersion < '4006000') {
	t3lib_extMgm::addPItoST43($_EXTKEY, 'pi_list/class.tx_ttboard_pi_list.php', '_pi_list', 'list_type', 1 /* cached */);
	t3lib_extMgm::addPItoST43($_EXTKEY, 'pi_tree/class.tx_ttboard_pi_tree.php', '_pi_tree', 'list_type', 1 /* cached */);


	// Define database backend as backend for 4.5 and below (default in 4.6)
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['backend'])) {
        $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['backend'] = 't3lib_cache_backend_DbBackend';
    }
	// Define data and tags table for 4.5 and below (obsolete in 4.6)
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options'])) {
        $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options'] = array();
    }
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options']['cacheTable'])) {
        $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options']['cacheTable'] = 'tt_board_cache';
    }
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options']['tagsTable'])) {
        $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tt_board_cache']['options']['tagsTable'] = 'tt_board_cache_tags';
    }
} else {
	// add missing setup for the tt_content "list_type = 3" which is used by tt_board
	$addLine = trim('
tt_content.list.20.2 = CASE
tt_content.list.20.2 {
    key.field = layout
    0 = < plugin.tt_board_tree
}');

	t3lib_extMgm::addTypoScript(
		TT_BOARD_EXTkey, 'setup', '
	# Setting ' . TT_BOARD_EXTkey . ' plugin TypoScript
	' . $addLine . '
	',
		43
	);

	$addLine = trim('
tt_content.list.20.4 = CASE
tt_content.list.20.4 {
    key.field = layout
    0 = < plugin.tt_board_list
    1 = < plugin.tt_board_tree
}');

	t3lib_extMgm::addTypoScript(
		TT_BOARD_EXTkey,
		'setup', '
	# Setting ' . TT_BOARD_EXTkey . ' plugin TypoScript
	' . $addLine . '
	',
		43
	);
}


?>