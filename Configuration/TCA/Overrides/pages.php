<?php

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    ExtensionManagementUtility::registerPageTSConfigFile(
        'tt_board',
        'Configuration/TsConfig/Page/Mod/Wizards/NewContentElement.tsconfig',
        'Message Board Content Element Wizards'
    );
});

