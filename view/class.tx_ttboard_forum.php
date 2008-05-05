<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 */


require_once (PATH_BE_ttboard.'marker/class.tx_ttboard_marker.php');
require_once (PATH_BE_ttboard.'model/class.tx_ttboard_model.php');


class tx_ttboard_forum {
	var $local_cObj;
	var $conf;
	var $typolink_conf;
	var $allowCaching;
	var $markerObj;
	var $pid;
	var $bHasBeenInitialised = FALSE;

	function init (&$local_cObj, &$conf, $allowCaching, &$typolink_conf, $pid)	{
		$this->local_cObj = &$local_cObj;
		$this->conf = &$conf;
		$this->allowCaching = $allowCaching;
		$this->typolink_conf = &$typolink_conf;
		$this->pid = $pid;
		$this->bHasBeenInitialised = TRUE;
	}

	function needsInit()	{
		return !$this->bHasBeenInitialised;
	}

	/**
	 * Creates the forum display, including listing all items/a single item
	 */
	function &printView($uid, $pid_list, $theCode, &$orig_templateCode, $alternativeLayouts)	{

		$modelObj = &t3lib_div::getUserObj('&tx_ttboard_model');
		$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');
		$recentPosts = array();
		if ($this->conf['iconCode'])	{
			$modelObj->treeIcons['joinBottom'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['joinBottom'],$this->conf['iconCode.']['joinBottom.']);
			$modelObj->treeIcons['join'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['join'],$this->conf['iconCode.']['join.']);
			$modelObj->treeIcons['line'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['line'],$this->conf['iconCode.']['line.']);
			$modelObj->treeIcons['blank'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['blank'],$this->conf['iconCode.']['blank.']);
			$modelObj->treeIcons['thread'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['thread'],$this->conf['iconCode.']['thread.']);
			$modelObj->treeIcons['end'] = $this->local_cObj->stdWrap($this->conf['iconCode.']['end'],$this->conf['iconCode.']['end.']);
		}

		if ($uid && $theCode=='FORUM')	{
			if (!$this->allowCaching)	{
				$GLOBALS['TSFE']->set_no_cache();	// MUST set no_cache as this displays single items and not a whole page....
			}
			$lConf = $this->conf['view_thread.'];
			$templateCode = $this->local_cObj->getSubpart($orig_templateCode, '###TEMPLATE_THREAD###');

			if ($templateCode)	{

					// Clear
				$subpartMarkerArray = array();
				$wrappedSubpartContentArray = array();
				$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');

					// Getting the specific parts of the template
				$markerArray = $markerObj->getColumnMarkers();
				$templateCode = $this->local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartMarkerArray,$wrappedSubpartContentArray);
				$rootParent = $modelObj->getRootParent($uid);
				$wholeThread = $modelObj->getSingleThread($rootParent['uid'],1);
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
				$markerArray['###FORUM_TITLE###'] = $this->local_cObj->stdWrap($GLOBALS['TSFE']->page['title'],$lConf['forum_title_stdWrap.']);

					// Link back to forum
				$this->local_cObj->setCurrentVal($this->pid);
				$wrappedSubpartContentArray['###LINK_BACK_TO_FORUM###'] = $this->local_cObj->typolinkWrap($this->typolink_conf);

					// Link to next thread
				$this->local_cObj->setCurrentVal($this->pid);
				$temp_conf=$this->typolink_conf;
				if (is_array($nextThread))	{
					$temp_conf['additionalParams'] .= '&tt_board_uid='.$nextThread['uid'];
					$temp_conf['useCacheHash'] = $this->allowCaching;
					$temp_conf['no_cache'] = !$this->allowCaching;
				}
				$wrappedSubpartContentArray['###LINK_NEXT_THREAD###'] = $this->local_cObj->typolinkWrap($temp_conf);

					// Link to prev thread
				$this->local_cObj->setCurrentVal($this->pid);
				$temp_conf = $this->typolink_conf;
				if (is_array($prevThread))	{
					$temp_conf['additionalParams'] .= '&tt_board_uid='.$prevThread['uid'];
					$temp_conf['useCacheHash'] = $this->allowCaching;
					$temp_conf['no_cache'] = !$this->allowCaching;
				}
				$wrappedSubpartContentArray['###LINK_PREV_THREAD###'] = $this->local_cObj->typolinkWrap($temp_conf);

					// Link to first !!
				$this->local_cObj->setCurrentVal($this->pid);
				$temp_conf = $this->typolink_conf;
				$temp_conf['additionalParams'].= '&tt_board_uid='.$rootParent['uid'];
				$temp_conf['useCacheHash'] = $this->allowCaching;
				$temp_conf['no_cache'] = !$this->allowCaching;
				$wrappedSubpartContentArray['###LINK_FIRST_POST###'] = $this->local_cObj->typolinkWrap($temp_conf);

					// Substitute:
				$templateCode = $this->local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,array(),$wrappedSubpartContentArray);

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

					$this->local_cObj->start($recentPost);

						// Markers
					$markerArray['###POST_THREAD_CODE###'] = $this->local_cObj->stdWrap($recentPost['treeIcons'], $lConf['post_thread_code_stdWrap.']);
					$markerArray['###POST_TITLE###'] = $this->local_cObj->stdWrap($markerObj->formatStr($recentPost['subject']), $lConf['post_title_stdWrap.']);
					$markerArray['###POST_CONTENT###'] = $markerObj->substituteEmoticons($this->local_cObj->stdWrap($markerObj->formatStr($recentPost['message']), $lConf['post_content_stdWrap.']));
					$markerArray['###POST_REPLIES###'] = $this->local_cObj->stdWrap($modelObj->getNumReplies($recentPost['pid'],$recentPost['uid']), $lConf['post_replies_stdWrap.']);
					$markerArray['###POST_AUTHOR###'] = $this->local_cObj->stdWrap($markerObj->formatStr($recentPost['author']), $lConf['post_author_stdWrap.']);
					$markerArray['###POST_AUTHOR_EMAIL###'] = $recentPost['email'];
					$markerArray['###POST_DATE###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['date_stdWrap.']);
					$markerArray['###POST_TIME###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['time_stdWrap.']);
					$markerArray['###POST_AGE###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['age_stdWrap.']);

						// Link to the post
					$this->local_cObj->setCurrentVal($recentPost['pid']);
					$temp_conf=$this->typolink_conf;
					$temp_conf['additionalParams'] .= '&tt_board_uid='.$recentPost['uid'];
					$temp_conf['useCacheHash'] = $this->allowCaching;
					$temp_conf['no_cache'] = !$this->allowCaching;
					$wrappedSubpartContentArray['###LINK###'] = $this->local_cObj->typolinkWrap($temp_conf);

						// Link to next thread
					$this->local_cObj->setCurrentVal($recentPost['pid']);
					$temp_conf = $this->typolink_conf;
					$temp_conf['additionalParams'] .= '&tt_board_uid='.($recentPost['nextUid']?$recentPost['nextUid']:$nextThread['uid']);
					$temp_conf['useCacheHash'] = $this->allowCaching;
					$temp_conf['no_cache'] = !$this->allowCaching;
					$wrappedSubpartContentArray['###LINK_NEXT_POST###'] = $this->local_cObj->typolinkWrap($temp_conf);

						// Link to prev thread
					$this->local_cObj->setCurrentVal($recentPost['pid']);
					$temp_conf = $this->typolink_conf;
					$temp_conf['additionalParams'] .= '&tt_board_uid='.($recentPost['prevUid']?$recentPost['prevUid']:$prevThread['uid']);
					$temp_conf['useCacheHash'] = $this->allowCaching;
					$temp_conf['no_cache'] = !$this->allowCaching;
					$wrappedSubpartContentArray['###LINK_PREV_POST###'] = $this->local_cObj->typolinkWrap($temp_conf);

						// Substitute:
					$subpartContent .= $this->local_cObj->substituteMarkerArrayCached($out,$markerArray,array(),$wrappedSubpartContentArray);
				}
				$GLOBALS['TSFE']->indexedDocTitle = $indexedTitle;
					// Substitution:
				$content .= $this->local_cObj->substituteSubpart($templateCode,'###CONTENT###',$subpartContent);
			} else {
				debug('No template code for ');
			}
		} else { // if ($this->tt_board_uid && $theCode=='FORUM')
			$continue = true;
			if ($theCode == 'THREAD_TREE')	{
				if (!$uid)	{
					$continue = false;
				}
				$lConf = $this->conf['thread_tree.'];
			} else {
				$lConf = $this->conf['list_threads.'];
			}
			if($continue) {
				$templateCode = $this->local_cObj->getSubpart($orig_templateCode, '###TEMPLATE_FORUM###');

				if ($templateCode)	{
						// Clear
					$subpartMarkerArray = array();
					$wrappedSubpartContentArray = array();
					$markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');

						// Getting the specific parts of the template
					$markerArray = $markerObj->getColumnMarkers();
					$markerArray['###FORUM_TITLE###'] = $this->local_cObj->stdWrap($GLOBALS['TSFE']->page['title'],$lConf['forum_title_stdWrap.']);
					$templateCode = $this->local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartMarkerArray,$wrappedSubpartContentArray);
					$postHeader = $markerObj->getLayouts($templateCode,$alternativeLayouts,'POST');
						// Template code used if tt_board_uid matches...
					$postHeader_active = $markerObj->getLayouts($templateCode,1,'POST_ACTIVE');
					$subpartContent = '';

					if ($theCode == 'THREAD_TREE')	{
						$rootParent = $modelObj->getRootParent($uid);
						$recentPosts = $modelObj->getSingleThread($rootParent['uid'],1);
					} else {
						$recentPosts = $modelObj->getThreads($pid_list,$this->conf['tree'], $lConf['thread_limit'] ? $lConf['thread_limit']:'50', t3lib_div::_GP('tt_board_sword'));
					}
					$c_post = 0;
					$subpartArray = array();
					foreach ($recentPosts as $k => $recentPost)	{
						$GLOBALS['TT']->push('/Post/');
						$out = $postHeader[$c_post%count($postHeader)];
						if ($recentPost['uid'] == $uid && $postHeader_active[0])	{
							$out = $postHeader_active[0];
						}
						$c_post++;
						$this->local_cObj->start($recentPost);

							// Clear
						$markerArray = array();
						$wrappedSubpartContentArray = array();

							// Markers
						$GLOBALS['TT']->push('/postMarkers/');
						$markerArray['###POST_THREAD_CODE###'] = $this->local_cObj->stdWrap($recentPost['treeIcons'], $lConf['post_thread_code_stdWrap.']);
						$markerArray['###POST_TITLE###'] = $this->local_cObj->stdWrap($markerObj->formatStr($recentPost['subject']), $lConf['post_title_stdWrap.']);
						$markerArray['###POST_CONTENT###'] = $markerObj->substituteEmoticons($this->local_cObj->stdWrap($markerObj->formatStr($recentPost['message']), $lConf['post_content_stdWrap.']));
						$markerArray['###POST_REPLIES###'] = $this->local_cObj->stdWrap($modelObj->getNumReplies($recentPost['pid'],$recentPost['uid']), $lConf['post_replies_stdWrap.']);
						$markerArray['###POST_AUTHOR###'] = $this->local_cObj->stdWrap($markerObj->formatStr($recentPost['author']), $lConf['post_author_stdWrap.']);
						$markerArray['###POST_DATE###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['date_stdWrap.']);
						$markerArray['###POST_TIME###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['time_stdWrap.']);
						$markerArray['###POST_AGE###'] = $this->local_cObj->stdWrap($modelObj->recentDate($recentPost),$this->conf['age_stdWrap.']);

							// Link to the post
						$this->local_cObj->setCurrentVal($recentPost['pid']);
						$temp_conf=$this->typolink_conf;
						$temp_conf['additionalParams'].= '&tt_board_uid='.$recentPost['uid'];
						$temp_conf['useCacheHash'] = $this->allowCaching;
						$temp_conf['no_cache'] = !$this->allowCaching;
						$wrappedSubpartContentArray['###LINK###'] = $this->local_cObj->typolinkWrap($temp_conf);
						$GLOBALS['TT']->pull();

							// Last post processing:
						$GLOBALS['TT']->push('/last post info/');
						$lastPostInfo = $modelObj->getLastPostInThread($recentPost['pid'],$recentPost['uid']);
						$GLOBALS['TT']->pull();
						if (!$lastPostInfo)	$lastPostInfo=$recentPost;

						$this->local_cObj->start($lastPostInfo);
						$GLOBALS['TT']->push('/lastPostMarkers/');
						$recentDate = $modelObj->recentDate($lastPostInfo);
						$markerArray['###LAST_POST_DATE###'] = $this->local_cObj->stdWrap($recentDate,$this->conf['date_stdWrap.']);
						$markerArray['###LAST_POST_TIME###'] = $this->local_cObj->stdWrap($recentDate,$this->conf['time_stdWrap.']);
						$markerArray['###LAST_POST_AGE###'] = $this->local_cObj->stdWrap($recentDate,$this->conf['age_stdWrap.']);
						$markerArray['###LAST_POST_AUTHOR###'] = $this->local_cObj->stdWrap($markerObj->formatStr($lastPostInfo['author']), $lConf['last_post_author_stdWrap.']);

							// Link to the last post
						$this->local_cObj->setCurrentVal($lastPostInfo['pid']);
						$temp_conf = $this->typolink_conf;
						$temp_conf['additionalParams'] .= '&tt_board_uid='.$lastPostInfo['uid'];
						$temp_conf['useCacheHash'] = $this->allowCaching;
						$temp_conf['no_cache'] = !$this->allowCaching;
						$wrappedSubpartContentArray['###LINK_LAST_POST###'] = $this->local_cObj->typolinkWrap($temp_conf);
						$GLOBALS['TT']->pull();

							// Substitute:
						$subpartArray[$recentDate.sprintf('%010d',$recentPost['uid'])] = $this->local_cObj->substituteMarkerArrayCached($out, $markerArray, array(), $wrappedSubpartContentArray);
						$GLOBALS['TT']->pull();
					}
					if (!$this->conf['tree'])	{
						krsort($subpartArray);
					}

						// Substitution:
					$markerArray = array();
					$subpartContentArray = array();
						// Fill in array
					$markerArray['###SEARCH_WORD###'] = $GLOBALS['TSFE']->no_cache ? t3lib_div::_GP('tt_board_sword') : '';		// Setting search words in field if cache is disabled.
						// Set FORM_URL
					$this->local_cObj->setCurrentVal($GLOBALS['TSFE']->id);
					$temp_conf = $this->typolink_conf;
					$temp_conf['no_cache'] = 1;
					$markerArray['###FORM_URL###'] = $this->local_cObj->typoLink_URL($temp_conf);
					$subpartContent = implode('',$subpartArray);

						// Substitute CONTENT-subpart
					$subpartContentArray['###CONTENT###'] = $subpartContent;
					$content.= $this->local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartContentArray);
				} else {
					debug('No template code for ');
				}
			} // if($continue){
		}
		return $content;
	} // forum_forum
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/view/class.tx_ttboard_forum.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/view/class.tx_ttboard_forum.php']);
}

?>
