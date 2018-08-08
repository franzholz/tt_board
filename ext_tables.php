<?php

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

call_user_func($emClass . '::addStaticFile', $_EXTKEY, 'Configuration/TypoScript/DefaultCSS/', 'Message Board CSS styles');
call_user_func($emClass . '::addStaticFile', $_EXTKEY, 'Configuration/TypoScript/Default/', 'Message Board Setup');

call_user_func($emClass . '::allowTableOnStandardPages', 'tt_board');
call_user_func($emClass . '::addToInsertRecords', 'tt_board');
call_user_func($emClass . '::addLLrefForTCAdescr', 'tt_board', 'EXT:' . $_EXTKEY . DIV2007_LANGUAGE_SUBPATH . 'locallang_csh_ttboard.xlf');

if (TYPO3_MODE == 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['JambageCom\\TtBoard\\Controller\\WizardIcon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Controller/WizardIcon.php';
}
