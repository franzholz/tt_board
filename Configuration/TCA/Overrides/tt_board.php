<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    $table = 'tt_board';
    $excludeArray = [];

    if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude.'])) {
        $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude.'];
    } else if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude'])) {
        $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude'];
    }

    if (
        isset($excludeArray) &&
        is_array($excludeArray) &&
        isset($excludeArray[$table])
    ) {
        $excludeArray[$table] =
            \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $excludeArray[$table]);
        \JambageCom\Div2007\Utility\TcaUtility::removeField(
            $GLOBALS['TCA'][$table],
            $excludeArray[$table]
        );
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords($table);
});

