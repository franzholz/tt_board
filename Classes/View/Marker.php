<?php

namespace JambageCom\TtBoard\View;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Franz Holzinger <franzt@ttproducts.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Part of the tt_board (Message Board) extension.
 *
 * marker functions
 *
 * @author	Franz Holzinger <franzt@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_products
 *
 */

use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Utility\ExtensionUtility;
use JambageCom\Div2007\Utility\FrontendUtility;
use JambageCom\Div2007\Utility\BrowserUtility;


class Marker
{
    protected $conf;
    protected $dontParseContent = 0;


    /**
    * Initialized the marker object
    *
    */
    public function init ($conf)
    {
        $this->setConf($conf);
        $this->dontParseContent = $conf['dontParseContent'];
    }

    public function setConf ($conf)
    {
        $this->conf = $conf;
    }

    public function getConf ()
    {
        return $this->conf;
    }

    /**
    * getting the global markers
    */
    public function getGlobalMarkers ($cObj)
    {
        $markerArray = array();
        $conf = $this->getConf();

        // globally substituted markers, fonts and colors.
        $splitMark = md5(microtime());
        for ($i = 1; $i <= 3; ++$i) {
            list(
                $markerArray['###GW' . $i . 'B###'],
                $markerArray['###GW' . $i . 'E###']
            ) =
                explode(
                    $splitMark,
                    $cObj->stdWrap(
                        $splitMark,
                        $conf['wrap' . $i . '.']
                    )
                );
        }
        for ($i = 1; $i <= 4; ++$i) {
            $markerArray['###GC' . $i . '###'] =
                $cObj->stdWrap(
                    $conf['color' . $i],
                    $conf['color' . $i . '.']
                );
        }
        $markerArray['###PATH###'] = PATH_FE_TTBOARD_REL;

        if (is_array($conf['marks.'])) {
                // Substitute Marker Array from TypoScript Setup
            foreach ($conf['marks.'] as $key => $value) {
                $markerArray['###' . $key . '###'] = $value;
            }
        }

            // Call all addURLMarkers hooks at the end of this method
        if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['addGlobalMarkers'])) {
            foreach  ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['addGlobalMarkers'] as $classRef) {
                $hookObj = GeneralUtility::makeInstance($classRef);
                if (method_exists($hookObj, 'addGlobalMarkers')) {
                    $hookObj->addGlobalMarkers($markerArray);
                }
            }
        }
        return $markerArray;
    } // getGlobalMarkers

    public function getRowMarkerArray (
        &$markerArray,
        $modelObj,
        $row,
        $markerKey,
        $lConf
    )
    {
        $conf = $this->getConf();
        $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        
        $local_cObj->start(
            $row,
            $modelObj->getTablename()
        );

            // Markers
        $markerArray['###POST_THREAD_CODE###'] =
            $local_cObj->stdWrap(
                $row['treeIcons'],
                $lConf['post_thread_code_stdWrap.']
            );
        $markerArray['###POST_TITLE###'] =
            $local_cObj->stdWrap(
                $this->formatStr(
                    $row['subject']
                ),
                $lConf['post_title_stdWrap.']
            );
        $markerArray['###POST_CONTENT###'] =
            $this->substituteEmoticons(
                $local_cObj->stdWrap(
                    $this->formatStr(
                        $row['message']),
                        $lConf['post_content_stdWrap.']
                    )
            );
        $markerArray['###POST_REPLIES###'] =
            $local_cObj->stdWrap(
                $modelObj->getNumReplies(
                    $row['pid'],
                    $row['uid']
                ),
                $lConf['post_replies_stdWrap.']
            );
        $markerArray['###POST_AUTHOR###'] =
            $local_cObj->stdWrap(
                $this->formatStr(
                    $row['author']
                ),
                $lConf['post_author_stdWrap.']
            );
        $markerArray['###POST_AUTHOR_EMAIL###'] = $recentPost['email'];
        $recentDate =
            $modelObj->recentDate($row);
        $markerArray['###POST_DATE###'] =
            $local_cObj->stdWrap(
                $recentDate,
                $conf['date_stdWrap.']
            );
        $markerArray['###POST_TIME###'] =
            $local_cObj->stdWrap(
                $recentDate,
                $conf['time_stdWrap.']
            );
        $markerArray['###POST_AGE###'] =
            $local_cObj->stdWrap(
                $recentDate,
                $conf['age_stdWrap.']
            );
    }

    public function getColumnMarkers (&$markerArray, $languageObj)
    {
        $locallang = $languageObj->getLocallang();

        foreach ($locallang['default'] as $k => $text) {
            if (strpos($k, 'board') === 0) {
                $markerArray['###' . strtoupper($k) . '###'] =
                    $languageObj->getLabel(
                        $k
                    );
            }
        }

        $markerArray['###BUTTON_SEARCH###'] =
            $languageObj->getLabel(
                'button_search'
            );
    }

    public function getIconMarkers (&$markerArray, $iconConfig)
    {
        $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);

        foreach ($iconConfig as $type => $renderType) {
            if (strpos($type, '.') !== false) {
                continue;
            }
            $config = $iconConfig[$type . '.'];
            $config['file'] =
                ExtensionUtility::getExtensionFilePath(
                    $config['file'],
                    true
                );
            $imageHtml =
                $local_cObj->getContentObject(
                    $renderType
                )->render(
                    $config
                );
            $markerArray['###IMAGE_' . strtoupper($type) . '###'] = $imageHtml;
        }
    }

    /**
    * Returns alternating layouts
    */
    public function getLayouts ($templateCode, $alternativeLayouts, $marker)
    {
        $templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        $out = array();
        for($a = 0; $a < $alternativeLayouts; $a++) {
            $m = '###' . $marker . ($a ? '_' . $a : '') . '###';
            if(strstr($templateCode, $m)) {
                $out[] = $templateService->getSubpart($templateCode, $m);
            } else {
                break;
            }
        }
        return $out;
    }

    /**
    * Format string with nl2br and htmlspecialchars()
    */
    public function formatStr ($str)
    {
        $result = '';
        if (!$this->dontParseContent) {
            $result = nl2br(htmlspecialchars($str));
        } else {
            $result = $str;
        }
        return $result;
    }

    /**
    * Emoticons substitution
    */
    public function substituteEmoticons ($str)
    {
        $conf = $this->getConf();

        if (
            isset($conf['emoticons']) &&
            $conf['emoticons'] &&
            isset($conf['emoticons.']) &&
            isset($conf['emoticons.']['substitute.']) &&
            isset($conf['emoticons.']['path']) &&
            isset($conf['emoticons.']['icon']) &&
            isset($conf['emoticons.']['icon.'])
        ) {
            $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
            $image = $conf['emoticons.']['icon.'];
            foreach ($conf['emoticons.']['substitute.'] as $key => $substitute) {
                if (
                    isset($substitute['source']) &&
                    isset($substitute['destination'])
                ) {
                    $source = $substitute['source'];
                    $image['file'] = $conf['emoticons.']['path'] . $substitute['destination'];
                    $destination = $local_cObj->getContentObject(
                        $conf['emoticons.']['icon']
                    )->render(
                        $image
                    );

                    $str =
                        str_replace(
                            $source,
                            $destination,
                            $str
                        );
                }
            }
        }
        return $str;
    }

    public function getBrowserObj (
        $conf,
        $browserConf,
        $recordCount,
        $piVars,
        $limit,
        $maxPages
    )
    {
        $bShowFirstLast = true;

        if (
            isset($browserConf) &&
            is_array($browserConf) &&
            isset($browserConf['showFirstLast'])
        ) {
            $bShowFirstLast = $browserConf['showFirstLast'];
        }

        $pagefloat = 0;
        $imageArray = array();
        $imageActiveArray = array();
        $browseObj = GeneralUtility::makeInstance(\JambageCom\Div2007\Base\BrowserBase::class);
        $browseObj->init(
            $conf,
            $piVars,
            array(),
            false,  // no autocache used yet
            false, // USER obj
            $recordCount,
            $limit,
            $maxPages,
            $bShowFirstLast,
            false,
            $pagefloat,
            $imageArray,
            $imageActiveArray
        );

        return $browseObj;
    }

    public function getBrowserMarkers (
        &$markerArray,
        &$subpartArray,
        &$wrappedSubpartArray,
        $cObj,
        $languageObj,
        $browserConf,
        $prefixId,
        $addQueryString,
        $recordCount,
        $piVars,
        $limit,
        $more,
        $pointerName,
        $begin_at,
        $useCache
    )
    {
        $browseObj =
            $this->getBrowserObj(
                $this->getConf(),
                $browserConf,
                $recordCount,
                $piVars,
                $limit,
                1000
            );

        $splitMark = md5(microtime());

        if ($more) {
            $next =
                (
                    $begin_at + $limit > $recordCount
                ) ?
                    $recordCount - $limit :
                    $begin_at + $limit;
            $addQueryString[$pointerName] = intval($next / $limit);
            $tempUrl =
                BrowserUtility::linkTPKeepCtrlVars(
                    $browseObj,
                    $cObj,
                    $prefixId,
                    $splitMark,
                    $addQueryString,
                    $useCache
                );

            $wrappedSubpartArray['###LINK_NEXT###'] = explode($splitMark, $tempUrl);
        } else {
            $subpartArray['###LINK_NEXT###'] = '';
        }

        if ($begin_at) {
            $prev = ($begin_at - $limit < 0) ? 0 : $begin_at - $limit;
            $addQueryString[$pointerName] = intval($prev / $limit);
            $tempUrl =
                BrowserUtility::linkTPKeepCtrlVars(
                    $browseObj,
                    $cObj,
                    $prefixId,
                    $splitMark,
                    $addQueryString,
                    $useCache
                );

            $wrappedSubpartArray['###LINK_PREV###'] = explode($splitMark, $tempUrl);
        } else {
            $subpartArray['###LINK_PREV###'] = '';
        }

        $markerArray['###BROWSE_LINKS###'] = '';

        if ($recordCount > $limit) { // there is more than one page, so let's browse

            if (is_array($browserConf)) {
                $wrappedSubpartArray['###LINK_BROWSE###'] = array('', '');

                $markerArray['###BROWSE_LINKS###'] =
                    BrowserUtility::render(
                        $browseObj,
                        $languageObj,
                        $cObj,
                        $prefixId,
                        true,
                        1,
                        '',
                        $browserConf,
                        $pointerName,
                        true,
                        $addQueryString
                    );
            }
            // ###CURRENT_PAGE### of ###TOTAL_PAGES###
            $markerArray['###CURRENT_PAGE###'] = intval($begin_at / $limit + 1);
            $markerArray['###TOTAL_PAGES###'] = ceil($recordCount / $limit);
        } else {
            $subpartArray['###LINK_BROWSE###'] = '';
            $markerArray['###CURRENT_PAGE###'] = '1';
            $markerArray['###TOTAL_PAGES###'] = '1';
        }
    }
}

