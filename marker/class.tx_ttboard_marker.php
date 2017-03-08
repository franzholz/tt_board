<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger <franzt@ttproducts.de>
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
 * @maintainer	Franz Holzinger <franzt@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_products
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class tx_ttboard_marker {
	public $pibase;
	public $cObj;
	public $cnf;
	public $conf;
	public $config;
	public $urlArray;

	public $emoticons = 1;
	public $emoticonsPath = 'media/emoticons/';
	public $emoticonsTag = '<img src="{}" valign="bottom" hspace=4>';
	public $emoticonsSubst = array(
		'>:-<' => 'angry.gif',
		':D' => 'grin.gif',
		':-(' => 'sad.gif',
		':-)' => 'smile.gif',
		':-P' => 'tongue.gif',
		';-P' => 'tonguewink.gif',
		':-D' => 'veryhappy.gif',
		';-)' => 'wink.gif'
	);

	public $dontParseContent = 0;


	/**
	 * Initialized the marker object
	 *
	 */

	public function init ($pibase, $conf, $config) {
 		$this->pibase = $pibase;
 		$this->cObj = $pibase->cObj;
 		$this->conf = $conf;
 		$this->config = $config;

		$this->dontParseContent = $this->conf['dontParseContent'];
	}


	/**
	 * getting the global markers
	 */
	public function getGlobalMarkers () {
		$markerArray = array();

			// globally substituted markers, fonts and colors.
		$splitMark = md5(microtime());
		list($markerArray['###GW1B###'], $markerArray['###GW1E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $conf['wrap1.']));
		list($markerArray['###GW2B###'], $markerArray['###GW2E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $conf['wrap2.']));
		list($markerArray['###GW3B###'], $markerArray['###GW3E###']) = explode($splitMark, $this->cObj->stdWrap($splitMark, $conf['wrap3.']));
		$markerArray['###GC1###'] = $this->cObj->stdWrap($this->conf['color1'], $this->conf['color1.']);
		$markerArray['###GC2###'] = $this->cObj->stdWrap($this->conf['color2'], $this->conf['color2.']);
		$markerArray['###GC3###'] = $this->cObj->stdWrap($this->conf['color3'], $this->conf['color3.']);
		$markerArray['###GC4###'] = $this->cObj->stdWrap($this->conf['color4'], $this->conf['color4.']);
		$markerArray['###PATH###'] = PATH_FE_TTBOARD_REL;

		if (is_array($this->conf['marks.'])) {
				// Substitute Marker Array from TypoScript Setup
			foreach ($this->conf['marks.'] as $key => $value) {
				$markerArray['###' . $key . '###'] = $value;
			}
		}

			// Call all addURLMarkers hooks at the end of this method
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['addGlobalMarkers'])) {
			foreach  ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['addGlobalMarkers'] as $classRef) {
				$hookObj= GeneralUtility::getUserObj($classRef);
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
	) {
		$local_cObj = GeneralUtility::getUserObj('&tx_div2007_cobj');
		$local_cObj->start($row);

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
		$markerArray['###POST_DATE###'] = $local_cObj->stdWrap($recentDate, $this->conf['date_stdWrap.']);
		$markerArray['###POST_TIME###'] = $local_cObj->stdWrap($recentDate, $this->conf['time_stdWrap.']);
		$markerArray['###POST_AGE###'] = $local_cObj->stdWrap($recentDate, $this->conf['age_stdWrap.']);
	}


	public function getColumnMarkers (&$markerArray, $languageObj) {
		$locallang = $languageObj->getLocallang();

		foreach ($locallang['default'] as $k => $text) {
			if (strpos($k, 'board') === 0) {
				$markerArray['###' . strtoupper($k) . '###'] =
					tx_div2007_alpha5::getLL_fh003(
						$languageObj,
						$k
					);
			}
		}

		$markerArray['###BUTTON_SEARCH###'] =
			tx_div2007_alpha5::getLL_fh003(
				$languageObj,
				'button_search'
			);
	}


	/**
	 * Returns alternating layouts
	 */
	public function getLayouts ($templateCode, $alternativeLayouts, $marker) {
		$out = array();
		for($a = 0; $a < $alternativeLayouts; $a++) {
			$m = '###' . $marker . ($a ? '_' . $a : '') . '###';
			if(strstr($templateCode, $m)) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart($templateCode, $m);
			} else {
				break;
			}
		}
		return $out;
	}


	/**
	 * Format string with nl2br and htmlspecialchars()
	 */
	public function formatStr ($str) {
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
    public function substituteEmoticons ($str) {
        if ($this->emoticons) {
            foreach($this->emoticonsSubst as $source => $dest) {
                $str =
                    str_replace(
                        $source,
                        str_replace(
                            '{}',
                            $this->emoticonsPath . $dest,
                            $this->emoticonsTag
                        ),
                        $str
                    );
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
    ) {
        $bShowFirstLast = TRUE;

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
        $browseObj = t3lib_div::getUserObj('&tx_div2007_alpha_browse_base');
        $browseObj->init_fh002(
            $conf,
            $piVars,
            array(),
            FALSE,  // no autocache used yet
            FALSE, // USER obj
            $recordCount,
            $limit,
            $maxPages,
            $bShowFirstLast,
            FALSE,
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
    ) {
        $browseObj =
            $this->getBrowserObj(
                $conf,
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
                tx_div2007_alpha5::linkTP_keepCtrlVars(
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
                tx_div2007_alpha5::linkTP_keepCtrlVars(
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
                    tx_div2007_alpha5::list_browseresults_fh004(
                        $browseObj,
                        $languageObj,
                        $cObj,
                        $prefixId,
                        TRUE,
                        1,
                        '',
                        $browserConf,
                        $pointerName,
                        TRUE,
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


