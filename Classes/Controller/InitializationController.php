<?php

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Kasper Skårhøj <kasperYYYY@typo3.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 *
 * Function library for a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use JambageCom\TtBoard\Domain\Composite;


class InitializationController implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
    * does the initialization stuff
    *
    * @param		Composite	  A composite object will be returned.
    * @param		string		  content string
    * @param		string		  configuration array
    * @return	  boolean  false in error case, true if successfull
    */
    public function init (
        &$composite,
        ContentObjectRenderer $cObj,
        $content,
        array $conf,
        $uid,
        $prefixId
    )
    {
        if (!ExtensionManagementUtility::isLoaded(DIV2007_EXT)) {
            $content = 'Error in Board Extension(' . TT_BOARD_EXT . '): Extension ' . DIV2007_EXT . ' has not been loaded.';
            return false;
        }

        $config = array();
        $composite = GeneralUtility::makeInstance(Composite::class);

        // *************************************
        // *** getting configuration values:
        // *************************************
        $composite->setConf($conf);
        $composite->setCObj($cObj);
        $composite->setPrefixId($prefixId);
        $ttboardParams = GeneralUtility::_GP($prefixId);

        if (
            isset($ttboardParams) &&
            is_array($ttboardParams) &&
            isset($ttboardParams['uid']) &&
            $ttboardParams['uid'] != ''
        ) {
            $tt_board_uid = intval($ttboardParams['uid']);
        }

        if ($uid) {
            $tt_board_uid = $uid;
        }

        $alternativeLayouts = intval($conf['alternatingLayouts']) > 0 ? intval($conf['alternatingLayouts']) : 2;
        $composite->setAlternativeLayouts($alternativeLayouts);

            // pid_list is the pid/list of pids from where to fetch the guest items.
        $tmp = trim($cObj->stdWrap($conf['pid_list'], $conf['pid_list.']));
        $pid_list = $config['pid_list'] = ($conf['pid_list'] ? $conf['pid_list'] : $tmp);
        $pid_list = ($pid_list ? $pid_list : $GLOBALS['TSFE']->id);

        $composite->setPidList($pid_list);

        // page where to go usually
        $pid = ($conf['PIDforum'] ? $conf['PIDforum'] : ($pid ? $pid : $GLOBALS['TSFE']->id));

        $composite->setPid($pid);

        $allowCaching = $conf['allowCaching'] ? 1 : 0;
        $composite->setAllowCaching($allowCaching);
        $languageObj = GeneralUtility::makeInstance(\JambageCom\TtBoard\Api\Localization::class);
        $languageObj->init(
            TT_BOARD_EXT,
            $conf['_LOCAL_LANG.'],
            DIV2007_LANGUAGE_SUBPATH
        );

        $languageObj->loadLocalLang(
            'EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf',
            false
        );
        $composite->setLanguageObj($languageObj);
        $markerObj = GeneralUtility::makeInstance(\JambageCom\TtBoard\View\Marker::class);
        $markerObj->init($conf);
        $composite->setMarkerObj($markerObj);
        $modelObj = GeneralUtility::makeInstance(\JambageCom\TtBoard\Domain\TtBoard::class);
        $modelObj->init();
        $composite->setModelObj($modelObj);

        $globalMarkerArray = $markerObj->getGlobalMarkers($cObj);
            // template is read.
        $absoluteFileName = $GLOBALS['TSFE']->tmpl->getFileName($conf['templateFile']);
        $orig_templateCode = file_get_contents($absoluteFileName);

        if (
            version_compare(TYPO3_version, '8.7.0', '<')
        ) {
                // Substitute Global Marker Array
            $orig_templateCode =
                $cObj->substituteMarkerArray(
                    $orig_templateCode,
                    $globalMarkerArray
                );
        } else {
            $templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
                // Substitute Global Marker Array
            $orig_templateCode =
                $templateService->substituteMarkerArray(
                    $orig_templateCode,
                    $globalMarkerArray
                );
        }

        $composite->setOrigTemplateCode($orig_templateCode);

            // TypoLink.
        $typolink_conf = $conf['typolink.'];
        $typolink_conf['parameter.']['current'] = 1;
        if (isset($conf['linkParams']) && is_array($conf['linkParams'])) {
            $additionalParams = $typolink_conf['additionalParams'];
            $linkParamArray = array();
            foreach ($conf['linkParams'] as $k => $v) {
                $linkParamArray[] = $k . '=' . $v;
            }
            $additionalParams = ($additionalParams != '' ? $additionalParams . '&' : '&') . implode('&', $linkParamArray);
            $typolink_conf['additionalParams'] = $additionalParams;
        }
        $typolink_conf['additionalParams'] = $cObj->stdWrap(
            $typolink_conf['additionalParams'],
            $typolink_conf['additionalParams.']
        );
        unset($typolink_conf['additionalParams.']);
        $composite->setTypolinkConf($typolink_conf);

        // *************************************
        // *** doing the things...:
        // *************************************

            // If the current record should be displayed.
        $config['displayCurrentRecord'] = $conf['displayCurrentRecord'];
        if ($config['displayCurrentRecord']) {
            $config['code'] = 'FORUM';
            $tt_board_uid = $cObj->data['uid'];
        }

        $composite->setConfig($config);
        $composite->setTtBoardUid($tt_board_uid);
    }
}

