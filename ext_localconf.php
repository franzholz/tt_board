<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (!defined ('TT_BOARD_EXT')) {
	define('TT_BOARD_EXT', $_EXTKEY);
}

if (!defined ('PATH_BE_TTBOARD')) {
	define('PATH_BE_TTBOARD', t3lib_extMgm::extPath($_EXTKEY));
}

if (!defined ('PATH_BE_TTBOARD_REL')) {
	define('PATH_BE_TTBOARD_REL', t3lib_extMgm::extRelPath($_EXTKEY));
}

if (!defined ('PATH_FE_TTBOARD_REL')) {
	define('PATH_FE_TTBOARD_REL', t3lib_extMgm::siteRelPath($_EXTKEY));
}

if (isset($_EXTCONF) && is_array($_EXTCONF)) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] = $_EXTCONF;
	if (isset($tmpArray) && is_array($tmpArray)) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] =
			array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY], $tmpArray);
	}
}

if (TYPO3_MODE == 'BE') {
	// replace the output of the former CODE field with the flexform
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][2][] =
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][4][] =
		'EXT:' . $_EXTKEY . '/hooks/class.tx_ttboard_hooks_cms.php:&tx_ttboard_hooks_cms->pmDrawItem';

	t3lib_extMgm::addUserTSConfig('options.saveDocNew.tt_board=1');

	## Extending TypoScript from static template uid=43 to set up userdefined tag:
// 	t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tt_board_list = < plugin.tt_board_list.CSS_editor ', 43);
// 	t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tt_board_tree = < plugin.tt_board_tree.CSS_editor ', 43);
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


	// add missing setup for the tt_content "list_type = 2" which is used by tt_board
$addLine = trim('
tt_content.list.20.2 = CASE
tt_content.list.20.2 {
    key.field = layout
    0 = < plugin.tt_board_tree
}');

t3lib_extMgm::addTypoScript(
	$_EXTKEY,
	'setup', '
	# Setting ' . $_EXTKEY . ' plugin TypoScript
	' . $addLine . '
	',
	43
);

	// add missing setup for the tt_content "list_type = 4" which is used by tt_board
$addLine = trim('
tt_content.list.20.4 = CASE
tt_content.list.20.4 {
    key.field = layout
    0 = < plugin.tt_board_list
    1 = < plugin.tt_board_tree
}');

t3lib_extMgm::addTypoScript(
	$_EXTKEY,
	'setup', '
# Setting ' . $_EXTKEY . ' plugin TypoScript
' . $addLine . '
',
	43
);

