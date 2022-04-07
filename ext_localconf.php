<?php
defined('TYPO3_MODE') || die('Access denied.');
defined('TYPO3_version') || die('The constant TYPO3_version is undefined in tt_board!');

call_user_func(function () {
    if (!defined ('TT_BOARD_EXT')) {
        define('TT_BOARD_EXT', 'tt_board');
    }

    if (!defined ('PATH_BE_TTBOARD')) {
        define('PATH_BE_TTBOARD', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(TT_BOARD_EXT));
    }

    if (!defined ('PATH_FE_TTBOARD_REL')) {
        define('PATH_FE_TTBOARD_REL', \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(PATH_BE_TTBOARD));
    }

    if (!defined ('TT_BOARD_CSS_PREFIX')) {
        define('TT_BOARD_CSS_PREFIX', 'tx-ttboard-');
    }

    $extensionConfiguration = [];

    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get(TT_BOARD_EXT);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT])
    ) {
        $tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT];
    } else if (isset($tmpArray)) {
        unset($tmpArray);
    }

    if (isset($extensionConfiguration) && is_array($extensionConfiguration)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT] = $extensionConfiguration;
        if (isset($tmpArray) && is_array($tmpArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT] =
                array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT], $tmpArray);
        }
    } else if (!isset($tmpArray)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT] = [];
    }

    if (TYPO3_MODE == 'BE') {
        // replace the output of the former CODE field with the flexform
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][2][] =
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][4][] =
            \JambageCom\TtBoard\Hooks\CmsBackend::class . '->pmDrawItem';

         \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tt_board=1');
    }

        // add missing setup for the tt_content "list_type = 2" which is used by the tt_board tree view forum
    $addLine = trim('
    tt_content.list.20.2 = CASE
    tt_content.list.20.2 {
        key.field = layout
        0 = < plugin.tt_board_tree
    }');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        TT_BOARD_EXT,
        'setup', '
        # Setting ' . TT_BOARD_EXT . ' plugin TypoScript
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

     \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        TT_BOARD_EXT,
        'setup', '
    # Setting ' . TT_BOARD_EXT . ' plugin TypoScript
    ' . $addLine . '
    ',
        43
    );

    // Configure captcha hooks
    if (
        !isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['captcha']) ||
        !is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['captcha'])
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['captcha'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['captcha'][] = 'JambageCom\\Div2007\\Captcha\\Captcha';
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['captcha'][] = 'JambageCom\\Div2007\\Captcha\\Freecap';
    }
});
