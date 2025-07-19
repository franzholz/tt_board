<?php

defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey): void {
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get($extensionKey);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey])
    ) {
        $tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey];
    } elseif (isset($tmpArray)) {
        unset($tmpArray);
    }

    if (isset($extensionConfiguration) && is_array($extensionConfiguration)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = $extensionConfiguration;
        if (isset($tmpArray) && is_array($tmpArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] =
                array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey], $tmpArray);
        }
    } elseif (!isset($tmpArray)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = [];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['ttBoardPluginListTypeToCTypeUpdate']
        = \JambageCom\TtBoard\Upgrades\PluginListTypeToCTypeUpdate::class;
}, 'tt_board');
