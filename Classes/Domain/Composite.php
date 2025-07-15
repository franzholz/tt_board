<?php
declare(strict_types=1);

namespace JambageCom\TtBoard\Domain;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
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
 * variable storage for a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author  Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author  Franz Holzinger <franz@ttproducts.de>
 */
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Composite implements SingletonInterface
{
    protected $cObj;       // The backReference to the mother cObj object set at call time
    protected $extensionKey = 'tt_board';
    protected $prefixId;
    protected $alternativeLayouts = '';
    protected $allowCaching = '';
    protected $conf = [];
    protected $config = [];
    protected string $pid_list;           // list of page ids

    protected $tt_board_uid = '';
    protected $pid = '';
    protected $orig_templateCode = '';
    protected $typolink_conf = [];

    protected $errorMessage = '';
    protected $languageObj = null;
    protected $markerObj = null;
    protected $modelObj = null;
    protected $request = null;


    public function setCObj($value): void
    {
        $this->cObj = $value;
    }

    public function getCObj()
    {
        return $this->cObj;
    }

    public function setExtensionKey($value): void
    {
        $this->extensionKey = $value;
    }

    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    public function setPrefixId($value): void
    {
        $this->prefixId = $value;
    }

    public function getPrefixId()
    {
        return $this->prefixId;
    }

    public function setAlternativeLayouts($value): void
    {
        $this->alternativeLayouts = $value;
    }

    public function getAlternativeLayouts()
    {
        return $this->alternativeLayouts;
    }

    public function setAllowCaching($value): void
    {
        $this->allowCaching = $value;
    }

    public function getAllowCaching()
    {
        return $this->allowCaching;
    }

    public function setConf($value): void
    {
        $this->conf = $value;
    }

    public function getConf()
    {
        return $this->conf;
    }

    public function setConfig($value): void
    {
        $this->config = $value;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setPidList($value): void
    {
        $this->pid_list = (string) $value;
    }

    public function getPidList()
    {
        return $this->pid_list;
    }

    public function setTtBoardUid($value): void
    {
        $this->tt_board_uid = $value;
    }

    public function getTtBoardUid()
    {
        return $this->tt_board_uid;
    }

    public function setPid($value): void
    {
        $this->pid = $value;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function setOrigTemplateCode($value): void
    {
        $this->orig_templateCode = $value;
    }

    public function getOrigTemplateCode()
    {
        return $this->orig_templateCode;
    }

    public function setTypolinkConf($value): void
    {
        $this->typolink_conf = $value;
    }

    public function getTypolinkConf()
    {
        return $this->typolink_conf;
    }

    public function setErrorMessage($value): void
    {
        $this->errorMessage = $value;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setLanguageObj($value): void
    {
        $this->languageObj = $value;
    }

    public function getLanguageObj()
    {
        return $this->languageObj;
    }

    public function setMarkerObj($value): void
    {
        $this->markerObj = $value;
    }

    public function getMarkerObj()
    {
        return $this->markerObj;
    }

    public function setModelObj($value): void
    {
        $this->modelObj = $value;
    }

    public function getModelObj()
    {
        return $this->modelObj;
    }

    public function setRequest($value): void
    {
        $this->request = $value;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
