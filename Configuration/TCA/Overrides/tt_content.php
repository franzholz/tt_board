<?php

defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function ($extensionKey, $table): void {
    $pluginArray = ['2' => 'tree', '4' => 'list'];

    $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionKey)));

    foreach ($pluginArray as $k => $pluginType) {
        $pluginSignature = strtolower($extensionName . '_' . $pluginType);

        ExtensionManagementUtility::addPlugin(
            [
                'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_tca.xlf:pi_' . $pluginType,
                $pluginSignature,
                'EXT:' . $extensionKey . '/Resources/Public/Icons/Extension.gif',
                'plugin'
            ],
            'CType',
            $extensionKey,
        );

        // Activate the display of the FlexForm field
        ExtensionManagementUtility::addToAllTCAtypes(
            'tt_content',
            '--div--;Configuration,pi_flexform,',
            $pluginSignature,
            'after:subheader',
        );

        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_ds_pi_' . $pluginType . '.xml',
            $pluginSignature,
        );
    }
}, 'tt_board', basename(__FILE__, '.php'));
