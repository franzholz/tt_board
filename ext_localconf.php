<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    $extensionKey = 'tt_board';

    if (!defined ('PATH_BE_TTBOARD')) {
        define('PATH_BE_TTBOARD', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey));
    }

    if (!defined ('PATH_FE_TTBOARD_REL')) {
        define('PATH_FE_TTBOARD_REL', \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(PATH_BE_TTBOARD));
    }

    if (!defined ('TT_BOARD_CSS_PREFIX')) {
        define('TT_BOARD_CSS_PREFIX', 'tx-ttboard-');
    }

    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get($extensionKey);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey])
    ) {
        $tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey];
    } else if (isset($tmpArray)) {
        unset($tmpArray);
    }

    if (isset($extensionConfiguration) && is_array($extensionConfiguration)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = $extensionConfiguration;
        if (isset($tmpArray) && is_array($tmpArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] =
                array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey], $tmpArray);
        }
    } else if (!isset($tmpArray)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = [];
    }
});
