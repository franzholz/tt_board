<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2009 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * class.tx_ttboard_forum.php
 *
 * Function library for a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * $Id$
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


require_once (PATH_BE_ttboard.'marker/class.tx_ttboard_marker.php');
require_once (PATH_BE_ttboard.'model/class.tx_ttboard_model.php');


class tx_ttboard_forum {
	var $conf;
	var $typolink_conf;
	var $allowCaching;
	var $markerObj;
	var $pid;
	var $bHasBeenInitialised = FALSE;
	var $pibase;


	function init (&$conf, $allowCaching, &$typolink_conf, $pid, &$pibase)	{
		$this->conf = &$conf;
		$this->allowCaching = $allowCaching;
		$this->typolink_conf = &$typolink_conf;
		$this->pid = $pid;
		$this->bHasBeenInitialised = TRUE;
		$this->pibase = &$pibase;
	}


	function needsInit ()	{
		return !$this->bHasBeenInitialised;
	}


	/**
	 * Creates the forum display, including listing all items/a single item
	 */
	function &printView ($uid, $ref, $pid_list, $theCode, &$orig_templateCode, $alternativeLayouts, $linkParams)	{
		global $TSFE;

		$modelObj = &t3lib_div::getUserObj('&tx_ttboard_model');
		$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');
		$local_cObj = &t3lib_div::getUserObj('&tx_div2007_cobj');
		$recentPosts = array();

		if ($this->conf['iconCode'])	{
			$modelObj->treeIcons['joinBottom'] = $local_cObj->stdWrap($this->conf['iconCode.']['joinBottom'],$this->conf['iconCode.']['joinBottom.']);
			$modelObj->treeIcons['join'] = $local_cObj->stdWrap($this->conf['iconCode.']['join'],$this->conf['iconCode.']['join.']);
			$modelObj->treeIcons['line'] = $local_cObj->stdWrap($this->conf['iconCode.']['line'],$this->conf['iconCode.']['line.']);
			$modelObj->treeIcons['blank'] = $local_cObj->stdWrap($this->conf['iconCode.']['blank'],$this->conf['iconCode.']['blank.']);
			$modelObj->treeIcons['thread'] = $local_cObj->stdWrap($this->conf['iconCode.']['thread'],$this->conf['iconCode.']['thread.']);
			$modelObj->treeIcons['end'] = $local_cObj->stdWrap($this->conf['iconCode.']['end'],$this->conf['iconCode.']['end.']);
		}

		if (($uid || $ref!='') && $theCode == 'FORUM')	{
			if (!$this->allowCaching)	{
				$TSFE->set_no_cache();	// MUST set no_cache as this displays single items and not a whole page....
			}
			$lConf = $this->conf['view_thread.'];
			$templateCode = $local_cObj->getSubpart($orig_templateCode, '###TEMPLATE_THREAD###');

			if ($templateCode)	{

					// Clear
				$subpartMarkerArray = array();
				$wrappedSubpartContentArray = array();
				$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');

					// Getting the specific parts of the template
				$markerArray = $markerObj->getColumnMarkers();
				$templateCode = $local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartMarkerArray,$wrappedSubpartContentArray);
				$rootParent = $modelObj->getRootParent($uid, $ref);
				$wholeThread = $modelObj->getSingleThread($rootParent['uid'],$ref,1);
				if ($lConf['single'])	{
					foreach ($wholeThread as $recentP)	{
						if ($recentP['uid']==$uid)	{
							$recentPosts[]=$recentP;
							break;
						}
					}
				} else {
					$recentPosts = $wholeThread;
				}
				$nextThread = $modelObj->getThreadRoot($pid_list,$rootParent);
				$prevThread = $modelObj->getThreadRoot($pid_list,$rootParent,'prev');
				$subpartContent='';

					// Clear
				$markerArray = array();
				$wrappedSubpartContentArray = array();

					// Getting the specific parts of the template
				$markerArray['###FORUM_TITLE###'] = $local_cObj->stdWrap($TSFE->page['title'],$lConf['forum_title_stdWrap.']);

					// Link back to forum
				$local_cObj->setCurrentVal($this->pid);
				$wrappedSubpartContentArray['###LINK_BACK_TO_FORUM###'] = $local_cObj->typolinkWrap($this->typolink_conf);

					// Link to next thread
				$linkParams[$this->pibase->prefixId.'[uid]'] = $nextThread['uid'];
				$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

/*

				$overrulePIvars = array(
					'uid' => $nextThread['uid']
				);



				$pageLink = htmlspecialchars(
					$this->pibase->pi_linkTP_keepPIvars_url(
						$overrulePIvars,
						$this->allowCaching,
						0,
						$this->pid
					)
				);*/
				$wrappedSubpartContentArray['###LINK_NEXT_THREAD###'] = array('<a href="'. $url .'">','</a>');

					// Link to prev thread
				$linkParams[$this->pibase->prefixId.'[uid]'] = $prevThread['uid'];
				$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

// 				$overrulePIvars = array(
// 					'uid' => $prevThread['uid']
// 				);
// 				$pageLink = htmlspecialchars(
// 					$this->pibase->pi_linkTP_keepPIvars_url(
// 						$overrulePIvars,
// 						$this->allowCaching,
// 						0,
// 						$this->pid
// 					)
// 				);

				$wrappedSubpartContentArray['###LINK_PREV_THREAD###'] = array('<a href="'. $url .'">','</a>');

					// Link to first !!
				$linkParams[$this->pibase->prefixId.'[uid]'] = $rootParent['uid'];
				$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

/*				$overrulePIvars = array(
					'uid' => ($rootParent['uid'])
				);
				$pageLink = htmlspecialchars(
					$this->pibase->pi_linkTP_keepPIvars_url(
						$overrulePIvars,
						$this->allowCaching,
						0,
						$this->pid
					)
				);*/
				$wrappedSubpartContentArray['###LINK_FIRST_POST###'] = array('<a href="'. $url .'">','</a>');

					// Substitute:
				$templateCode = $local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,array(),$wrappedSubpartContentArray);

					// Getting subpart for items:
				$postHeader = $markerObj->getLayouts($templateCode,$alternativeLayouts,'POST');
				$c_post=0;
				$indexedTitle='';

				foreach ($recentPosts as $recentPost)	{
					$out = $postHeader[$c_post%count($postHeader)];
					$c_post++;
					if (!$indexedTitle && trim($recentPost['subject']))	$indexedTitle = trim($recentPost['subject']);

						// Clear
					$markerArray=array();
					$wrappedSubpartContentArray=array();

					$markerObj->getRowMarkerArray (
						$recentPost,
						'POST',
						$markerArray,
						$lConf
					);

						// Link to the post
					$linkParams[$this->pibase->prefixId.'[uid]'] = $recentPost['uid'];
					$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

// 					$overrulePIvars = array(
// 						'uid' => ($recentPost['uid'])
// 					);
// 					$pageLink = htmlspecialchars(
// 						$this->pibase->pi_linkTP_keepPIvars_url(
// 							$overrulePIvars,
// 							$this->allowCaching,
// 							0,
// 							$this->pid
// 						)
// 					);
					$wrappedSubpartContentArray['###LINK###'] = array('<a href="'. $url .'">','</a>');

						// Link to next thread
					$linkParams[$this->pibase->prefixId.'[uid]'] = ($recentPost['nextUid']?$recentPost['nextUid']:$nextThread['uid']);
					$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

// 					$overrulePIvars = array(
// 						'uid' => ($recentPost['nextUid']?$recentPost['nextUid']:$nextThread['uid'])
// 					);
// 					$pageLink = htmlspecialchars(
// 						$this->pibase->pi_linkTP_keepPIvars_url(
// 							$overrulePIvars,
// 							$this->allowCaching,
// 							0,
// 							$this->pid
// 						)
// 					);
					$wrappedSubpartContentArray['###LINK_NEXT_POST###'] = array('<a href="'. $url .'">','</a>');

						// Link to prev thread
					$linkParams[$this->pibase->prefixId.'[uid]'] = ($recentPost['prevUid']?$recentPost['prevUid']:$nextThread['uid']);
					$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

// 					$overrulePIvars = array(
// 						'uid' => ($recentPost['prevUid']?$recentPost['prevUid']:$nextThread['uid'])
// 					);
// 					$pageLink = htmlspecialchars(
// 						$this->pibase->pi_linkTP_keepPIvars_url(
// 							$overrulePIvars,
// 							$this->allowCaching,
// 							0,
// 							$this->pid
// 						)
// 					);
					$wrappedSubpartContentArray['###LINK_PREV_POST###'] = array('<a href="'. $url .'">','</a>');

						// Substitute:
					$subpartContent .= $local_cObj->substituteMarkerArrayCached($out,$markerArray,array(),$wrappedSubpartContentArray);
				}
				$TSFE->indexedDocTitle = $indexedTitle;
					// Substitution:
				$content .= $local_cObj->substituteSubpart($templateCode,'###CONTENT###',$subpartContent);
			} else {
				debug('No template code for ');
			}
		} else { // if ($this->tt_board_uid && $theCode=='FORUM')
			$continue = TRUE;
			if ($theCode == 'THREAD_TREE')	{
				if (!$uid && $ref == '')	{
					$continue = FALSE;
				}
				$lConf = $this->conf['thread_tree.'];
			} else {
				$lConf = $this->conf['list_threads.'];
			}

			if($continue) {
				$templateCode = $local_cObj->getSubpart($orig_templateCode, '###TEMPLATE_FORUM###');

				if ($templateCode)	{
						// Clear
					$subpartMarkerArray = array();
					$wrappedSubpartContentArray = array();
					$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');

						// Getting the specific parts of the template
					$markerArray = $markerObj->getColumnMarkers();
					$markerArray['###FORUM_TITLE###'] = $local_cObj->stdWrap($TSFE->page['title'],$lConf['forum_title_stdWrap.']);

					$templateCode = $local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartMarkerArray,$wrappedSubpartContentArray);
					$postHeader = $markerObj->getLayouts($templateCode,$alternativeLayouts,'POST');
						// Template code used if tt_board_uid matches...
					$postHeader_active = $markerObj->getLayouts($templateCode,1,'POST_ACTIVE');
					$subpartContent = '';

					if ($theCode == 'THREAD_TREE')	{
						$rootParent = $modelObj->getRootParent($uid, $ref);
						$recentPosts = $modelObj->getSingleThread($rootParent['uid'],$ref,1);
					} else {
						$recentPosts = $modelObj->getThreads($pid_list,$ref,$this->conf['tree'], $lConf['thread_limit'] ? $lConf['thread_limit']:'50', t3lib_div::_GP('tt_board_sword'));
					}
					$c_post = 0;
					$subpartArray = array();

					foreach ($recentPosts as $recentPost)	{
						$GLOBALS['TT']->push('/Post/');
						$out = $postHeader[$c_post%count($postHeader)];
						if ($recentPost['uid'] == $uid && $postHeader_active[0])	{
							$out = $postHeader_active[0];
						}
						$c_post++;
						$local_cObj->start($recentPost);

							// Clear
						$markerArray = array();
						$wrappedSubpartContentArray = array();

							// Markers
						$GLOBALS['TT']->push('/postMarkers/');
						$markerObj->getRowMarkerArray (
							$recentPost,
							'POST',
							$markerArray,
							$lConf
						);

							// Link to the post
						$overrulePIvars = array(
							'uid' => ($recentPost['uid'])
						);

						$linkParams[$this->pibase->prefixId.'[uid]'] = $recentPost['uid'];
						$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));


// 						$pageLink = htmlspecialchars(
// 							$this->pibase->pi_linkTP_keepPIvars_url(
// 								$overrulePIvars,
// 								$this->allowCaching,
// 								0,
// 								$this->pid
// 							)
// 						);
						$wrappedSubpartContentArray['###LINK###'] = array('<a href="'. $url .'">','</a>');

						$GLOBALS['TT']->pull();
							// Last post processing:
						$GLOBALS['TT']->push('/last post info/');
						$lastPostInfo = $modelObj->getLastPostInThread($recentPost['pid'],$recentPost['uid'],$ref);
						$GLOBALS['TT']->pull();
						if (!$lastPostInfo)	$lastPostInfo=$recentPost;

						$local_cObj->start($lastPostInfo);
						$GLOBALS['TT']->push('/lastPostMarkers/');
						$recentDate = $modelObj->recentDate($lastPostInfo);
						$markerArray['###LAST_POST_DATE###'] = $local_cObj->stdWrap($recentDate,$this->conf['date_stdWrap.']);
						$markerArray['###LAST_POST_TIME###'] = $local_cObj->stdWrap($recentDate,$this->conf['time_stdWrap.']);
						$markerArray['###LAST_POST_AGE###'] = $local_cObj->stdWrap($recentDate,$this->conf['age_stdWrap.']);
						$markerArray['###LAST_POST_AUTHOR###'] = $local_cObj->stdWrap($markerObj->formatStr($lastPostInfo['author']), $lConf['last_post_author_stdWrap.']);

							// Link to the last post
						$linkParams[$this->pibase->prefixId.'[uid]'] = $lastPostInfo['uid'];
						$url = tx_div2007_alpha::getPageLink_fh002($local_cObj,$this->pid,'',$linkParams,array('useCacheHash' => $this->allowCaching));

/*
						$overrulePIvars = array(
							'uid' => ($lastPostInfo['uid'])
						);
						$pageLink = htmlspecialchars(
							$this->pibase->pi_linkTP_keepPIvars_url(
								$overrulePIvars,
								$this->allowCaching,
								0,
								$this->pid
							)
						);*/
						$wrappedSubpartContentArray['###LINK_LAST_POST###'] = array('<a href="'. $url .'">','</a>');

						$GLOBALS['TT']->pull();
							// Substitute:
						$subpartArray[$recentDate.sprintf('%010d',$recentPost['uid'])] = $local_cObj->substituteMarkerArrayCached($out, $markerArray, array(), $wrappedSubpartContentArray);
						$GLOBALS['TT']->pull();
					}
					if (!$this->conf['tree'])	{
						krsort($subpartArray);
					}

						// Substitution:
					$markerArray = array();
					$subpartContentArray = array();
						// Fill in array
					$markerArray['###SEARCH_WORD###'] = $TSFE->no_cache ? t3lib_div::_GP('tt_board_sword') : '';		// Setting search words in field if cache is disabled.
						// Set FORM_URL
					$local_cObj->setCurrentVal($TSFE->id);
					$temp_conf = $this->typolink_conf;
					$temp_conf['no_cache'] = 1;
					$markerArray['###FORM_URL###'] = $local_cObj->typoLink_URL($temp_conf);
					$subpartContent = implode('',$subpartArray);

						// Substitute CONTENT-subpart
					$subpartContentArray['###CONTENT###'] = $subpartContent;
					$content .= $local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartContentArray);
				} else {
					debug('No template code for ');
				}
			} // if($continue){
		}
		return $content;
	} // forum_forum
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tt_board/view/class.tx_ttboard_forum.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tt_board/view/class.tx_ttboard_forum.php']);
}

?>
