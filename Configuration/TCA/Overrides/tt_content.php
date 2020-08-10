<?php
defined('TYPO3_MODE') || die('Access denied.');

$table = 'tt_content';

$GLOBALS['TCA'][$table]['types']['list']['subtypes_excludelist']['2'] = 'layout,select_key';
$GLOBALS['TCA'][$table]['types']['list']['subtypes_addlist']['2'] = 'pi_flexform';

$GLOBALS['TCA'][$table]['types']['list']['subtypes_excludelist']['4'] = 'layout,select_key';
$GLOBALS['TCA'][$table]['types']['list']['subtypes_addlist']['4'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('2', 'FILE:EXT:' . TT_BOARD_EXT . '/Configuration/FlexForms/flexform_ds_pi_tree.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('4', 'FILE:EXT:' . TT_BOARD_EXT . '/Configuration/FlexForms/flexform_ds_pi_list.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:pi_tree',
        '2',
        'EXT:' . TT_BOARD_EXT . '/ext_icon.gif'
    ],
    'list_type',
    TT_BOARD_EXT
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_tca.xlf:pi_list',
        '4',
        'EXT:' . TT_BOARD_EXT . '/ext_icon.gif'
    ],
    'list_type',
    TT_BOARD_EXT
);

