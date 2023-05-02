<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function($extensionKey)
{
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/DefaultCSS/',
        'Message Board CSS styles'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/Default/',
        'Message Board Setup'
    );
}, 'tt_board');

