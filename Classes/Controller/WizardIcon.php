<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


namespace JambageCom\TtBoard\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class that adds the wizard icon.
 *
 * @category    Plugin
 * @package     TYPO3
 * @subpackage  tt_board
 * @author      Franz Holzinger <franz@ttproducts.de>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class WizardIcon
{
    /**
     * Processes the wizard items array.
     *
     * @param array $wizardItems The wizard items
     * @return array Modified array with wizard items
     */
    public function proc (array $wizardItems)
    {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconRegistry');
        $iconPath = 'Resources/Public/Icons/';

        $wizardArray = array(
            'tree' => array(
                    'list_type' => 2,
                    'wizard_icon' => 'forum.gif'
                ),
            'list' => array(
                    'list_type' => 4,
                    'wizard_icon' => 'message_board.gif'
                )
        );

        foreach ($wizardArray as $type => $wizardConf) {
            $params = '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=' . $wizardConf['list_type'] . '&defVals[tt_content][select_key]=' . rawurlencode('FORUM, POSTFORM');
            $wizardItem = array(
                'title' => $GLOBALS['LANG']->sL('LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_be.xlf:plugins_' . $type . '_title'),
                'description' => $GLOBALS['LANG']->sL('LLL:EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang_be.xlf:plugins_' . $type . '_description'),
                'params' => $params
            );
            $iconIdentifier = 'extensions-tt_board-' . $type . '-wizard';
            $iconRegistry->registerIcon(
                $iconIdentifier,
                'TYPO3\\CMS\\Core\\Imaging\\IconProvider\\BitmapIconProvider',
                array(
                    'source' => 'EXT:' . TT_BOARD_EXT . '/' . $iconPath . $wizardConf['wizard_icon'],
                )
            );
            $wizardItem['iconIdentifier'] = $iconIdentifier;
            $wizardItems['plugins_ttboard_' . $type] = $wizardItem;
        }
        return $wizardItems;
    }
}
