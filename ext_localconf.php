<?php
defined('TYPO3') || die('Access denied.');

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
});
