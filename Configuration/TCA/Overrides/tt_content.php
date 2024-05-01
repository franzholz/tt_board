<?php

defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey, $table): void {
    $GLOBALS['TCA'][$table]['types']['list']['subtypes_excludelist']['2'] = 'layout';
    $GLOBALS['TCA'][$table]['types']['list']['subtypes_addlist']['2'] = 'pi_flexform';

    $GLOBALS['TCA'][$table]['types']['list']['subtypes_excludelist']['4'] = 'layout';
    $GLOBALS['TCA'][$table]['types']['list']['subtypes_addlist']['4'] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('2', 'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_ds_pi_tree.xml');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('4', 'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_ds_pi_list.xml');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_tca.xlf:pi_tree',
            '2',
            'EXT:' . $extensionKey . '/Resources/Public/Icons/Extension.gif'
        ],
        'list_type',
        $extensionKey
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_tca.xlf:pi_list',
            '4',
            'EXT:' . $extensionKey . '/Resources/Public/Icons/Extension.gif'
        ],
        'list_type',
        $extensionKey
    );
}, 'tt_board', basename(__FILE__, '.php'));
