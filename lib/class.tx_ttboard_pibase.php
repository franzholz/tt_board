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
 * tx_ttboard_pibase
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
 * @author	Franz Holzinger <contact@fholzinger.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once (PATH_BE_ttboard.'marker/class.tx_ttboard_marker.php');
require_once (PATH_BE_ttboard.'model/class.tx_ttboard_model.php');

class tx_ttboard_pibase extends tslib_pibase {
	var $extKey = TT_BOARD_EXTkey;	// The extension key.
	var $cObj;		// The backReference to the mother cObj object set at call time

	var $alternativeLayouts='';
	var $allowCaching='';
	var $conf=array();
	var $config=array();
	var $pid_list;			// list of page ids

	var $tt_board_uid='';
	var $pid='';
	var $orig_templateCode='';
	var $typolink_conf=array();
	var $local_cObj='';

	var $errorMessage;
	var $markerObj;
	var $freeCap;

	/**
	 * does the initialization stuff
	 *
	 * @param		string		  content string
	 * @param		string		  configuration array
	 * @param		string		  modified configuration array
	 * @return	  void
	 */
	function init (&$content,&$conf,&$config) {
		global $TSFE;

		// *************************************
		// *** getting configuration values:
		// *************************************

		$this->conf = &$conf;
		$this->config = &$config;
		if (t3lib_extMgm::isLoaded(DIV2007_EXTkey))	{
			include_once (PATH_BE_div2007.'class.tx_div2007_alpha.php');
			include_once (PATH_BE_div2007.'class.tx_div2007_ff.php');
		}

		if (t3lib_extMgm::isLoaded(DIV2007_EXTkey)) {
			tx_div2007_alpha::loadLL_fh001($this,'EXT:'.$this->extKey.'/share/locallang.xml');
		}

		$this->tt_board_uid = intval(t3lib_div::_GP('tt_board_uid'));
		$this->alternativeLayouts = intval($this->conf['alternatingLayouts'])>0 ? intval($this->conf['alternatingLayouts']) : 2;
	
			// pid_list is the pid/list of pids from where to fetch the guest items.
		$tmp = trim($this->cObj->stdWrap($conf['pid_list'],$conf['pid_list.']));
		$pid_list = $config['pid_list'] = ($conf['pid_list'] ? $conf['pid_list'] :trim($this->cObj->stdWrap($conf['pid_list'],$conf['pid_list.'])));
		$this->pid_list = ($pid_list ? $pid_list : $TSFE->id);
		// page where to go usually
		$this->pid = ($conf['PIDforum'] ? $conf['PIDforum'] : $TSFE->id);

			// template is read.
		$this->orig_templateCode = $this->cObj->fileResource($conf['templateFile']);
		$this->allowCaching = $this->conf['allowCaching'] ? 1 : 0;
		$this->markerObj = &t3lib_div::getUserObj('&tx_ttboard_marker');
		$this->markerObj->init($this, $conf, $config);
		$this->modelObj = &t3lib_div::getUserObj('&tx_ttboard_model');
		$this->modelObj->init($this->cObj);

		$globalMarkerArray = $this->markerObj->getGlobalMarkers();
			// Substitute Global Marker Array
		$this->orig_templateCode= $this->cObj->substituteMarkerArray($this->orig_templateCode, $globalMarkerArray);

			// TypoLink.
		$this->typolink_conf = $this->conf['typolink.'];
		$this->typolink_conf['parameter.']['current'] = 1;
		$this->typolink_conf['additionalParams'] = $this->cObj->stdWrap(
			$this->typolink_conf['additionalParams'],
			$this->typolink_conf['additionalParams.']
		);
		unset($this->typolink_conf['additionalParams.']);

		// *************************************
		// *** doing the things...:
		// *************************************

		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');		// Local cObj.

			// If the current record should be displayed.
		$config['displayCurrentRecord'] = $conf['displayCurrentRecord'];
		if ($config['displayCurrentRecord'])	{
			$config['code']='FORUM';
			$this->tt_board_uid = $this->cObj->data['uid'];
		}

		// *************************************
		// *** doing the things...:
		// *************************************
		// tt_guest parts:
//		$this->recordCount = $this->getRecordCount($this->pid_list);
//		$globalMarkerArray['###PREVNEXT###'] = $this->getPrevNext();

		// all extensions:

			// Substitute Global Marker Array
		$this->orig_templateCode = $this->cObj->substituteMarkerArray($this->orig_templateCode, $globalMarkerArray);

		if ($this->conf['captcha'] == 'freecap' && t3lib_extMgm::isLoaded('sr_freecap') ) {
			require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
			$this->freeCap = &t3lib_div::getUserObj('&tx_srfreecap_pi2');
		}
	}

	function getCodeArray(&$conf)	{
		$config = array();
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['useFlexforms']) {

				// check the flexform
			$this->pi_initPIflexForm();
			require_once(PATH_BE_div2007.'class.tx_div2007_alpha.php');
			$config['code'] = tx_div2007_alpha::getSetupOrFFvalue_fh001(
				$this,
				$conf['code'],
				$conf['code.'],
				$conf['defaultCode'],
				$this->cObj->data['pi_flexform'],
				'display_mode',
				$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['useFlexforms']
			);
		} else {
				// 'CODE' decides what is rendered:
			$config['code'] = $this->cObj->stdWrap($conf['code'],$conf['code.']);
			$config['code'] = $config['code'] ? $config['code'] : $conf['defaultCode'];
		}

		$codeArray=t3lib_div::trimExplode(',', $config['code'],1);
		if (!count($codeArray))	{
			$codeArray=array('');
		}
		return ($codeArray);
	}

	function processCode($theCode, &$content)	{
		global $TSFE;

		switch($theCode)	{
			case 'LIST_CATEGORIES':
			case 'LIST_FORUMS':
				$content.= $this->forum_list($theCode);
			break;
			case 'POSTFORM':
			case 'POSTFORM_REPLY':
			case 'POSTFORM_THREAD':
				$content.= $this->forum_postform($theCode, $this->pid);
			break;
			case 'FORUM':
			case 'THREAD_TREE':
				include_once (PATH_BE_ttboard.'view/class.tx_ttboard_forum.php');
				$forumViewObj = &t3lib_div::getUserObj('&tx_ttboard_forum');
				if ($forumViewObj->needsInit())	{
					$forumViewObj->init($this->local_cObj, $this->conf, $this->allowCaching, $this->typolink_conf, $this->pid);
				}
				$content.= $forumViewObj->printView(
					$this->tt_board_uid,
					$this->pid_list,
					$theCode,
					$this->orig_templateCode,
					$this->alternativeLayouts
				);
			break;
			default:
				$contentTmp = 'error';
			break;
		}	// Switch

		if ($contentTmp == 'error') {
			$fileName = 'EXT:'.TT_BOARD_EXTkey.'/template/board_help.tmpl';
			$helpTemplate = $this->cObj->fileResource($fileName);
			if (t3lib_extMgm::isLoaded(DIV2007_EXTkey)) {

				$content .= tx_div2007_alpha::displayHelpPage_fh001(
					$this,
					$helpTemplate,
					TT_BOARD_EXTkey,
					$this->errorMessage,
					$theCode
				);
				unset($this->errorMessage);
			}
		}
	}


	/**
	 * Creates a list of forums or categories depending on theCode
	 */
	function forum_list($theCode)	{

		if (!$this->tt_board_uid)	{
			$forumlist=0;		// set to true if this is a list of forums and not categories + forums
			if ($theCode == 'LIST_CATEGORIES')	{
					// Config if categories are listed.
				$lConf = $this->conf['list_categories.'];
			} else {
				$forumlist = 1;
					// Config if forums are listed.
				$lConf = $this->conf['list_forums.'];
				$lConf['noForums'] = 0;
			}
			$GLOBALS['TSFE']->set_cache_timeout_default($lConf['cache_timeout'] ? intval($lConf['cache_timeout']) : 60*5);
			$templateCode = $this->local_cObj->getSubpart($this->orig_templateCode, '###TEMPLATE_OVERVIEW###');

			if ($templateCode)	{
					// Clear
				$subpartMarkerArray = array();
				$wrappedSubpartContentArray = array();

					// Getting the specific parts of the template

				$markerArray = $this->markerObj->getColumnMarkers();
				$templateCode = $this->local_cObj->substituteMarkerArrayCached($templateCode,$markerArray,$subpartMarkerArray,$wrappedSubpartContentArray);

					// Getting the specific parts of the template
				$categoryHeader = $this->markerObj->getLayouts($templateCode,$this->alternativeLayouts,'CATEGORY');
				$forumHeader = $this->markerObj->getLayouts($templateCode,$this->alternativeLayouts,'FORUM');
				$postHeader = $this->markerObj->getLayouts($templateCode,$this->alternativeLayouts,'POST');
				$subpartContent = '';

					// Getting categories
				$categories = $this->modelObj->getPagesInPage($this->pid_list);
				reset($categories);
				$c_cat=0;
				foreach ($categories as $k => $catData)	{
						// Getting forums in category
					if ($forumlist)	{
						$forums = $categories;
					} else {
						$forums = $this->modelObj->getPagesInPage($catData['uid']);
					}
					if (!$forumlist && count($categoryHeader))	{
							// Rendering category
						$out=$categoryHeader[$c_cat%count($categoryHeader)];
						$c_cat++;
						$this->local_cObj->start($catData);

							// Clear
						$markerArray=array();
						$wrappedSubpartContentArray=array();

							// Markers
						$markerArray['###CATEGORY_TITLE###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($catData['title']), $lConf['title_stdWrap.']);
						$markerArray['###CATEGORY_DESCRIPTION###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($catData['subtitle']), $lConf['subtitle_stdWrap.']);
						$markerArray['###CATEGORY_FORUMNUMBER###'] = $this->local_cObj->stdWrap(count($forums), $lConf['count_stdWrap.']);

							// Link to the category (wrap)
						$this->local_cObj->setCurrentVal($catData['uid']);
						$wrappedSubpartContentArray['###LINK###'] = $this->local_cObj->typolinkWrap($this->typolink_conf);

							// Substitute
						$subpartContent .= $this->local_cObj->substituteMarkerArrayCached($out,$markerArray,array(),$wrappedSubpartContentArray);
					}
					if (count($forumHeader) && !$lConf['noForums'])	{
							// Rendering forums
						$c_forum=0;
						foreach($forums as $forumData)	{
							$out=$forumHeader[$c_forum%count($forumHeader)];
							$c_forum++;
							$this->local_cObj->start($forumData);

								// Clear
							$markerArray=array();
							$wrappedSubpartContentArray=array();

								// Markers
							$markerArray['###FORUM_TITLE###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($forumData['title']), $lConf['forum_title_stdWrap.']);
							$markerArray['###FORUM_DESCRIPTION###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($forumData['subtitle']), $lConf['forum_description_stdWrap.']);
							$markerArray['###FORUM_POSTS###'] = $this->local_cObj->stdWrap($this->modelObj->getNumPosts($forumData['uid']), $lConf['forum_posts_stdWrap.']);
							$markerArray['###FORUM_THREADS###'] = $this->local_cObj->stdWrap($this->modelObj->getNumThreads($forumData['uid']), $lConf['forum_threads_stdWrap.']);

								// Link to the forum (wrap)
							$this->local_cObj->setCurrentVal($forumData['uid']);
							$wrappedSubpartContentArray['###LINK###'] = $this->local_cObj->typolinkWrap($this->typolink_conf);

								// LAST POST:
							$lastPostInfo = $this->modelObj->getLastPost($forumData['uid']);
							$this->local_cObj->start($lastPostInfo);
							if ($lastPostInfo)	{
								$markerArray['###LAST_POST_AUTHOR###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($lastPostInfo['author']), $lConf['last_post_author_stdWrap.']);
								$markerArray['###LAST_POST_DATE###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($lastPostInfo),$this->conf['date_stdWrap.']);
								$markerArray['###LAST_POST_TIME###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($lastPostInfo),$this->conf['time_stdWrap.']);
								$markerArray['###LAST_POST_AGE###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($lastPostInfo),$this->conf['age_stdWrap.']);
							} else {
								$markerArray['###LAST_POST_AUTHOR###'] = '';
								$markerArray['###LAST_POST_DATE###'] = '';
								$markerArray['###LAST_POST_TIME###'] = '';
								$markerArray['###LAST_POST_AGE###'] = '';
							}

								// Link to the last post
							$this->local_cObj->setCurrentVal($lastPostInfo['pid']);
							$temp_conf=$this->typolink_conf;
							$temp_conf['additionalParams'].= '&tt_board_uid='.$lastPostInfo['uid'];
							$temp_conf['useCacheHash'] = $this->allowCaching;
							$temp_conf['no_cache'] = !$this->allowCaching;
							$wrappedSubpartContentArray['###LINK_LAST_POST###'] = $this->local_cObj->typolinkWrap($temp_conf);

								// Add result
							$subpartContent .= $this->local_cObj->substituteMarkerArrayCached($out,$markerArray,array(),$wrappedSubpartContentArray);

								// Rendering the most recent posts
							if (count($postHeader) && $lConf['numberOfRecentPosts'])	{
								$recentPosts = $this->modelObj->getMostRecentPosts($forumData['uid'],intval($lConf['numberOfRecentPosts']));
								$c_post=0;
								foreach($recentPosts as $recentPost)	{
									$out=$postHeader[$c_post%count($postHeader)];
									$c_post++;
									$this->local_cObj->start($recentPost);

										// Clear:
									$markerArray=array();
									$wrappedSubpartContentArray=array();

										// markers:
									$markerArray['###POST_TITLE###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($recentPost['subject']), $lConf['post_title_stdWrap.']);
									$markerArray['###POST_CONTENT###'] = $this->substituteEmoticons($this->local_cObj->stdWrap($this->markerObj->formatStr($recentPost['message']), $lConf['post_content_stdWrap.']));
									$markerArray['###POST_REPLIES###'] = $this->local_cObj->stdWrap($this->modelObj->getNumReplies($recentPost['pid'],$recentPost['uid']), $lConf['post_replies_stdWrap.']);
									$markerArray['###POST_AUTHOR###'] = $this->local_cObj->stdWrap($this->markerObj->formatStr($recentPost['author']), $lConf['post_author_stdWrap.']);
									$markerArray['###POST_DATE###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($recentPost),$this->conf['date_stdWrap.']);
									$markerArray['###POST_TIME###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($recentPost),$this->conf['time_stdWrap.']);
									$markerArray['###POST_AGE###'] = $this->local_cObj->stdWrap($this->modelObj->recentDate($recentPost),$this->conf['age_stdWrap.']);

										// Link to the post:
									$this->local_cObj->setCurrentVal($recentPost['pid']);
									$temp_conf=$this->typolink_conf;
									$temp_conf['additionalParams'].= '&tt_board_uid='.$recentPost['uid'];
									$temp_conf['useCacheHash']=$this->allowCaching;
									$temp_conf['no_cache']=!$this->allowCaching;
									$wrappedSubpartContentArray['###LINK###']=$this->local_cObj->typolinkWrap($temp_conf);
									$subpartContent.=$this->local_cObj->substituteMarkerArrayCached($out,$markerArray,array(),$wrappedSubpartContentArray);
										// add result
									#$subpartContent.=$out;	// 250902
								}
							}
						}
					}
					if ($forumlist)	{
						break;
					}
				}
					// Substitution:
				$content.= $this->local_cObj->substituteSubpart($templateCode,'###CONTENT###',$subpartContent) ;
			} else {
				$content = $this->outMessage('No template code for ###TEMPLATE_OVERVIEW###');
			}
		}
		return $content;
	}


	/**
	 * Creates a post form for a forum
	 */
	function forum_postform($theCode, $pid)	{
		global $TSFE;

		$content = '';

		if ($this->modelObj->isAllowed($this->conf['memberOfGroups']))	{
			$parent=0;		// This is the parent item for the form. If this ends up being is set, then the form is a reply and not a new post.
			$nofity=array();
				// Find parent, if any
			if ($this->tt_board_uid)	{
				if ($this->conf['tree'])	{
					$parent = $this->tt_board_uid;
				} else {
					$parentR = $this->modelObj->getRootParent($this->tt_board_uid);
					$parent = $parentR['uid'];
				}

				$rootParent = $this->modelObj->getRootParent($parent);
				$wholeThread = $this->modelObj->getSingleThread($rootParent['uid'],1);
				foreach($wholeThread as $recordP)	{
					if ($recordP['notify_me'] && $recordP['email'])		{
						$notify[md5(trim(strtolower($recordP['email'])))] = trim($recordP['email']);
					}
				}
			}

				// Get the render-code
			$lConf = $this->conf['postform.'];

//   postform.dataArray {
//     10.label = Subject:
//     10.type = *data[tt_board][NEW][subject]=input,60
//     20.label = Message:
//     20.type =  *data[tt_board][NEW][message]=textarea,60
//     30.label = Name:
//     30.type = *data[tt_board][NEW][author]=input,40
//     40.label = Email:
//     40.type = *data[tt_board][NEW][email]=input,40
//     50.label = Notify me<BR>by reply: 
//     50.type = data[tt_board][NEW][notify_me]=check
//     60.type = formtype_db=submit
//     60.value = Post Reply
//   }

			$setupArray = array('10' => 'subject', '20' => 'message', '30' => 'author', '40' => 'email', '50' => 'notify_me', '60' => 'post_reply');

			$modEmail = $this->conf['moderatorEmail'];
			if (!$parent && isset($this->conf['postform_newThread.']))	{
				$lConf = $this->conf['postform_newThread.'] ? $this->conf['postform_newThread.'] : $lConf;			// Special form for newThread posts...
				$modEmail = $this->conf['moderatorEmail_newThread'] ? $this->conf['moderatorEmail_newThread'] : $modEmail;
				$setupArray['60'] = 'post_new_reply';
			}
			if ($modEmail)	{
				$modEmail = explode(',', $modEmail);
				foreach($modEmail as $modEmail_s)	{
					$notify[md5(trim(strtolower($modEmail_s)))] = trim($modEmail_s);
				}
			}
			if ($theCode=='POSTFORM' || ($theCode=='POSTFORM_REPLY' && $parent) || ($theCode=='POSTFORM_THREAD' && !$parent))	{
				$lConf['dataArray.']['9999.'] = array(
					'type' => '*data[tt_board][NEW][parent]=hidden',
					'value' => $parent
				);
				$lConf['dataArray.']['9998.'] = array(
					'type' => '*data[tt_board][NEW][pid]=hidden',
					'value' => $pid
				);
				$lConf['dataArray.']['9997.'] = array(
					'type' => 'tt_board_uid=hidden',
					'value' => $parent
				);
				if (is_object($this->freeCap))	{
					$freecapMarker = $this->freeCap->makeCaptcha();
					$lConf['dataArray.']['55.'] = array(
						'label' => $freecapMarker['###SR_FREECAP_IMAGE###'] . '<br>' . $freecapMarker['###SR_FREECAP_NOTICE###']. '<br>' . $freecapMarker['###SR_FREECAP_CANT_READ###'],
						'type' => '*data[tt_board][NEW][captcha]=input,60'
					);
				}
				if (count($notify))	{
					$lConf['dataArray.']['9997.'] = array(
						'type' => 'notify_me=hidden',
						'value' => htmlspecialchars(implode($notify,','))
					);
				}
				if (is_array($TSFE->fe_user->user))	{
					foreach ($lConf['dataArray.'] as $k => $dataRow)	{
						if (strpos($dataRow['type'],'[author]') !== FALSE)	{
							$lConf['dataArray.'][$k]['value'] = $TSFE->fe_user->user['name'];
						} else if (strpos($dataRow['type'],'[email]') !== FALSE)	{
							$lConf['dataArray.'][$k]['value'] = $TSFE->fe_user->user['email'];
						}
					}
				}

				foreach ($setupArray as $k => $type)	{
					if ($k == '60')	{
						$field = 'value';
					} else {
						$field = 'label';
					}
					if (is_array($lConf['dataArray.'][$k.'.']))	{
						if (
							(!$this->LLkey || $this->LLkey=='en') && !$lConf['dataArray.'][$k.'.'][$field] || 
							($this->LLkey!='en' && 
								!is_array($lConf['dataArray.'][$k.'.'][$field.'.']) ||  !is_array($lConf['dataArray.'][$k.'.'][$field.'.']['lang.']) || !is_array($lConf['dataArray.'][$k.'.'][$field.'.']['lang.'][$this->LLkey.'.'])
							)
						) {
							$lConf['dataArray.'][$k.'.'][$field] = $this->pi_getLL($type);
						}
					}
				}
				ksort ($lConf['dataArray.']);
				$content.=$this->local_cObj->FORM($lConf);
			}
		}
		return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/lib/class.tx_ttboard_pibase.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/lib/class.tx_ttboard_pibase.php']);
}

?>
