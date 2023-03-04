<?php

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    ExtensionManagementUtility::registerPageTSConfigFile(
        'tt_board',
        'Configuration/TSconfig/Page/Mod/Wizards/NewContentElement.tsconfig',
        'New Content Element Wizards'
    );
});

