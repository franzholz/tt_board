<?php

defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function ($extensionKey, $table): void {

    $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['exclude'];

    if (
        isset($excludeArray) &&
        is_array($excludeArray) &&
        isset($excludeArray[$table]) &&
        is_array($excludeArray[$table])
    ) {
        \JambageCom\Div2007\Utility\TcaUtility::removeField(
            $GLOBALS['TCA'][$table],
            $excludeArray[$table]
        );
    }

    ExtensionManagementUtility::addToInsertRecords($table);
}, 'tt_board', basename(__FILE__, '.php'));
