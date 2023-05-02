<?php

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function($extensionKey)
{
    ExtensionManagementUtility::registerPageTSConfigFile(
        $extensionKey,
        'Configuration/TsConfig/Page/Mod/Wizards/NewContentElement.tsconfig',
        'Message Board Content Element Wizards'
    );
}, 'tt_board');

