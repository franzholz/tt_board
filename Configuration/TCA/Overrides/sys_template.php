<?php
defined('TYPO3_MODE') || die('Access denied.');

if (!defined ('TT_BOARD_EXT')) {
    define('TT_BOARD_EXT', 'tt_board');
}


if (
    TYPO3_MODE == 'BE'
) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        TT_BOARD_EXT,
        'Configuration/TypoScript/DefaultCSS/',
        'Message Board CSS styles'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        TT_BOARD_EXT,
        'Configuration/TypoScript/Default/',
        'Message Board Setup'
    );
}

