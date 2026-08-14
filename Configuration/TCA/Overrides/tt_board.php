<?php

defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

use JambageCom\TtBoard\Utility\TcaModifier;


call_user_func(function ($extensionKey, $table): void {
    ExtensionManagementUtility::addToInsertRecords($table);
    TcaModifier::removeExcludedFields($GLOBALS['TCA'][$table], $table);
}, 'tt_board', basename(__FILE__, '.php'));
