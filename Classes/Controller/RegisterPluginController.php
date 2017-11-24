<?php

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger <franz@ttproducts.de>
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
 * boardLib.inc
 *
 * Creates a forum/board in tree or list style
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class RegisterPluginController extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

    /**
     * The backReference to the mother cObj object set at call time
     *
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * Should be same as classname of the plugin, used for CSS classes, variables
     *
     * @var string
     */
    public $prefixId;

    /**
     * Should normally be set in the main function with the TypoScript content passed to the method.
     *
     * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
     * $conf[userFunc] / $conf[includeLibs]  reserved for setting up the USER / USER_INT object. See TSref
     *
     * @var array
     */
    public $conf = array();


    /**
    * Main board function. Call this from TypoScript
    */
    public function main ($content, $conf) {
        $this->conf = $conf;
        $codeArray = $this->getCodeArray($conf);

        foreach ($codeArray as $k => $theCode) {
            $theCode = (string) strtoupper(trim($theCode));

            if (
                !isset($conf['userFunc.']) ||
                !isset($conf['userFunc.'][$theCode])
            ) {
                continue;
            }
            $setupCode = $conf['userFunc.'][$theCode];

            $setup = $conf['userFunc.'][$theCode . '.'];
            $newConf = $conf;

            ArrayUtility::mergeRecursiveWithOverrule(
                $newConf,
                $setup['10.']
            );
            unset($newConf['userFunc.']);

            $newSetup = array();
            $newSetup['10'] = $setup['10'];
            $newSetup['10.'] = $newConf;
            $content .=
                $this->cObj->cObjGetSingle(
                    $setupCode,
                    $newSetup
                );
        }

        return $content;
    }


    public function init ($content, $conf) {

        $initialization = GeneralUtility::makeInstance(
            \JambageCom\TtBoard\Controller\InitializationController::class
        );
        $initialization->init(
            $composite,
            $this->cObj,
            $content,
            $conf,
            $this->piVars['uid'],
            $this->prefixId
        );

        return $composite;
    }


    public function processCode ($theCode, &$content, $composite) {
        $action = GeneralUtility::makeInstance(
            \JambageCom\TtBoard\Controller\ActionController::class
        );
        $action->processCode($theCode, $content, $composite);
    }


    public function getCodeArray ($conf) {
        $config = array();

            // check the flexform
        $this->pi_initPIflexForm();
        $config['code'] = \tx_div2007_alpha5::getSetupOrFFvalue_fh004(
            $this->cObj,
            $conf['code'],
            $conf['code.'],
            $conf['defaultCode'],
            $this->cObj->data['pi_flexform'],
            'display_mode',
            true
        );

        $codeArray = GeneralUtility::trimExplode(
            ',',
            $config['code'],
            1
        );
        if (!count($codeArray)) {
            $codeArray = array('');
        }
        return ($codeArray);
    }


    public function help ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('HELP', $content, $composite);
        return $content;
    }


    public function listCategories ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('LIST_CATEGORIES', $content, $composite);
        return $content;
    }


    public function listForums ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('LIST_FORUMS', $content, $composite);
        return $content;
    }


    public function forum ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('FORUM', $content, $composite);
        return $content;
    }


    public function postForm ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('POSTFORM', $content, $composite);
        return $content;
    }


    public function postFormReply ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('POSTFORM_REPLY', $content, $composite);
        return $content;
    }


    public function thread ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('POSTFORM_THREAD', $content, $composite);
        return $content;
    }


    public function threadTree ($content, $conf) {
        $composite = $this->init($content, $conf);
        $this->processCode('THREAD_TREE', $content, $composite);
        return $content;
    }
}

