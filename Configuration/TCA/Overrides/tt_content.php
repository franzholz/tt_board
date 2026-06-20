<?php

defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function ($extensionKey, $table): void {
    $pluginArray = ['tree', 'list'];

    $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionKey)));

    foreach ($pluginArray as $pluginType) {
        $pluginSignature = strtolower($extensionName . '_' . $pluginType);

        ExtensionManagementUtility::addPlugin(
            [
                'label' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_tca.xlf:pi_' . $pluginType,
                'value' => $pluginSignature,
                'icon' => 'tt-board-' . $pluginType ,
                'group' => 'plugin',
                'description' => 'tt_board plugin ' . $pluginType
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
