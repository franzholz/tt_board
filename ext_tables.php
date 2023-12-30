<?php

defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey) {
    $table = 'tt_board';
    $languageSubpath = '/Resources/Private/Language/';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($table);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        $table,
        'EXT:' . $extensionKey . $languageSubpath . 'locallang_csh_ttboard.xlf'
    );
}, 'tt_board');
