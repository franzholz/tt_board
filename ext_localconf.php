<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (!defined ('TT_BOARD_EXT')) {
    define('TT_BOARD_EXT', $_EXTKEY);
}

if (!defined ('PATH_BE_TTBOARD')) {
    define('PATH_BE_TTBOARD', call_user_func($emClass . '::extPath', $_EXTKEY));
}

if (!defined ('PATH_BE_TTBOARD_REL')) {
    define('PATH_BE_TTBOARD_REL', call_user_func($emClass . '::extRelPath', $_EXTKEY));
}

if (!defined ('PATH_FE_TTBOARD_REL')) {
    define('PATH_FE_TTBOARD_REL', call_user_func($emClass . '::siteRelPath', $_EXTKEY));
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
        'JambageCom\\TtBoard\\Hooks\\CmsBackend->pmDrawItem';

    call_user_func($emClass . '::addUserTSConfig', 'options.saveDocNew.tt_board=1');
}


    // add missing setup for the tt_content "list_type = 2" which is used by the tt_board tree view forum
$addLine = trim('
tt_content.list.20.2 = CASE
tt_content.list.20.2 {
    key.field = layout
    0 = < plugin.tt_board_tree
}');

call_user_func(
    $emClass . '::addTypoScript',
    $_EXTKEY,
    'setup', '
    # Setting ' . $_EXTKEY . ' plugin TypoScript
    ' . $addLine . '
    ',
    43
);

    // add missing setup for the tt_content "list_type = 4" which is used by the tt_board list view forum
$addLine = trim('
tt_content.list.20.4 = CASE
tt_content.list.20.4 {
    key.field = layout
    0 = < plugin.tt_board_list
    1 = < plugin.tt_board_tree
}');

call_user_func(
    $emClass . '::addTypoScript',
    $_EXTKEY,
    'setup', '
# Setting ' . $_EXTKEY . ' plugin TypoScript
' . $addLine . '
',
    43
);

