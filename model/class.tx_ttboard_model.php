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
 * boardLib.inc
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


class tx_ttboard_model {

	var $treeIcons=array(
		'joinBottom'=>'\\-',
		'join'=>'|-',
		'line'=>'|&nbsp;',
		'blank'=>'&nbsp;&nbsp;',
		'thread'=>'+',
		'end'=>'-'
	);
	var $enableFields = '';		// The enablefields of the tt_board table.
	var $searchFieldList='author,email,subject,message';
	var $cObj;


	function init (&$cObj)	{
		$this->cObj = &$cObj;
		$this->enableFields = $this->cObj->enableFields('tt_board');
	}

	/**
	 * Checks if posting is allowed to user
	 */
	function isAllowed ($memberOfGroups)	{
		global $TSFE;

		$allowed = FALSE;
		if ($memberOfGroups)	{

			if (is_array($TSFE->fe_user->user))	{
				$requestGroupArray = t3lib_div::trimExplode(',', $memberOfGroups);
				$usergroupArray = explode(',',$TSFE->fe_user->user['usergroup']);
				$fitArray = array_intersect($requestGroupArray, $usergroupArray);
				if (count($fitArray))	{
					$allowed = TRUE;
				}
			} else {
				$allowed = FALSE;
			}
		} else {
			$allowed = TRUE;
		}
		return $allowed;
	}

	/**
	 * Get a record tree of forum items
	 */
	function getRecordTree (&$theRows,$parent,$pid,$treeIcons='') {
		global $TYPO3_DB;

		$where = 'pid=' . intval($pid) . ' AND parent=' . intval($parent) . $this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy());
		$c = 0;
		$rc = $TYPO3_DB->sql_num_rows($res);
		$prevUid = end(array_keys($theRows));
		$theRows[$prevUid]['treeIcons'] .= ($rc ? $this->treeIcons['thread'] : $this->treeIcons['end']);

		while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			$c++;
			$uid = $row['uid'];
			// check for a loop
			if (isset($theRows[$uid]))	{
				break;
			}
			$row['treeIcons'] = $treeIcons . ($rc==$c ? $this->treeIcons['joinBottom'] : $this->treeIcons['join']);
				// prev/next item:
			$theRows[$prevUid]['nextUid'] = $uid;
			$row['prevUid'] = $theRows[$prevUid]['uid'];
			$theRows[$uid] = $row;
				// get the branch
			$this->getRecordTree($theRows, $uid, $row['pid'], $treeIcons . ($rc==$c ? $this->treeIcons['blank'] : $this->treeIcons['line']));
			$prevUid = $uid;
		}
		$TYPO3_DB->sql_free_result($res);
	}

	/**
	 * Get subpages
	 *
	 * This function returns an array a pagerecords from the page-uid's in the pid_list supplied.
	 * Excludes pages, that would normally not enter a regular menu. That means hidden, timed or deleted pages + pages with another doktype than 'standard' or 'advanced'
	 */
	function getPagesInPage ($pid_list)	{
		global $TSFE;

		$thePids = t3lib_div::intExplode(',', $pid_list);
		$rcArray = array();
		foreach($thePids as $p_uid)	{
			$rcArray = array_merge($rcArray, $TSFE->sys_page->getMenu($p_uid));
		}
			// Exclude pages not of doktype 'Standard' or 'Advanced'
		foreach($rcArray as $key => $data)	{
			if (!t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['FE']['content_doktypes'], $data['doktype']))	{
				unset($rcArray[$key]);
			} // All pages including pages 'not in menu'
		}
		return $rcArray;
	}

	/**
	 * Returns number of post in a forum.
	 */
	function getNumPosts ($pid)	{
		global $TYPO3_DB;

		$where = 'pid IN ('.$pid.')'.$this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('count(*)', 'tt_board', $where);
		$row = $TYPO3_DB->sql_fetch_row($res);
		$TYPO3_DB->sql_free_result($res);
		return $row[0];
	}

	/**
	 * Returns number of threads.
	 */
	function getNumThreads ($pid)	{
		global $TYPO3_DB;

		$where = 'pid IN ('.$pid.') AND parent=0'.$this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('count(*)', 'tt_board', $where);
		$row = $TYPO3_DB->sql_fetch_row($res);
		$TYPO3_DB->sql_free_result($res);
		return $row[0];
	}

	/**
	 * Returns number of replies.
	 */
	function getNumReplies ($pid,$uid)	{
		global $TYPO3_DB;

		$where = 'pid IN (' . $pid . ') AND parent=' . intval($uid) . $this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('count(*)', 'tt_board', $where);
		$row = $TYPO3_DB->sql_fetch_row($res);
		$TYPO3_DB->sql_free_result($res);
		return $row[0];
	}

	/**
	 * Returns last post.
	 */
	function getLastPost ($pid)	{
		global $TYPO3_DB;

		$where = 'pid IN ('.$pid.')'.$this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy('DESC'), '1');
		$row = $TYPO3_DB->sql_fetch_assoc($res);
		$TYPO3_DB->sql_free_result($res);
		return $row;
	}

	/**
	 * Returns last post in thread.
	 */
	function getLastPostInThread ($pid,$uid)	{
		global $TYPO3_DB;

		$where = 'pid IN (' . $pid . ') AND parent=' . $uid . $this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy('DESC'), '1');
		$row = $TYPO3_DB->sql_fetch_assoc($res);
		$TYPO3_DB->sql_free_result($res);
		return $row;
	}

	/**
	 * Most recent posts.
	 *
	 * Returns an array with records
	 */
	function getMostRecentPosts ($pid,$number)	{
		global $TYPO3_DB;

		$where = 'pid IN ('.$pid.')'.$this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy('DESC'), $number);
		$out = array();
		while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			$out[] = $row;
		}
		$TYPO3_DB->sql_free_result($res);
		return $out;
	}

	/**
	 * Returns an array with threads
	 */
	function getThreads ($pid,$descend=0,$limit=100,$searchWord)	{
		global $TYPO3_DB;

		$outArray=array();
		if ($searchWord)	{
			$where = $this->cObj->searchWhere($searchWord, $this->searchFieldList, 'tt_board');
			$where = 'pid IN ('.$pid.') '.$where.$this->enableFields;
			$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy('DESC'), intval($limit));
			$set = array();
			while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
				$rootRow = $this->getRootParent($row['uid']);
				if (is_array($rootRow) && !isset($set[$rootRow['uid']]))	{
					$set[$rootRow['uid']] = 1;
					$outArray[$rootRow['uid']] = $rootRow;
					if ($descend)	{
						$this->getRecordTree($outArray,$rootRow['uid'],$rootRow['pid']);
					}
				}
			}
			$TYPO3_DB->sql_free_result($res);
		} else {
			$where = 'pid IN ('.$pid.') AND parent=0'.$this->enableFields;
			$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy('DESC'), intval($limit));
			while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
				$outArray[$row['uid']] = $row;
				if ($descend)	{
					$this->getRecordTree($outArray,$row['uid'],$row['pid']);
				}
			}
			$TYPO3_DB->sql_free_result($res);
		}
		return $outArray;
	}

	/**
	 * Returns records in a thread
	 */
	function getSingleThread ($uid,$decend=0)	{
		global $TYPO3_DB;

		$hash = md5($uid.'|'.$decend);
		if ($this->cache_thread[$hash])	{
			return $this->cache_thread[$hash];
		}

		$outArray = array();
		if ($uid)	{
			$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', 'uid='.$uid.$this->enableFields);
			if ($row = $TYPO3_DB->sql_fetch_assoc($res))	{

				$outArray[$row['uid']] = $row;
				if ($decend)	{
					$this->getRecordTree($outArray,$row['uid'],$row['pid']);
				}
			}
			$TYPO3_DB->sql_free_result($res);
		}
		return $outArray;
	}

	/**
	 * Get root parent of a tt_board record.
	 */
	function getRootParent ($uid,$limit=99)	{
		global $TYPO3_DB;

		if ($limit > 0)	{
			$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', 'uid='.$uid.$this->enableFields);
			if($row = $TYPO3_DB->sql_fetch_assoc($res))	{
				if ($row['parent'])	{
					$tmpRow = $this->getRootParent($row['parent'],$limit-1);
					if ($tmpRow)	{
						$row = $tmpRow;
					}
				}
			}
			$TYPO3_DB->sql_free_result($res);
		}
		return $row;
	}

	/**
	 * Returns next or prev thread in a tree
	 */
	function getThreadRoot ($pid,$rootParent,$type='next')	{
		global $TYPO3_DB;

		$datePart = ' AND crdate'.($type!='next'?'>':'<').intval($rootParent['crdate']);
		$where = 'pid IN ('.$pid.') AND parent=0'.$datePart.$this->enableFields;
		$res = $TYPO3_DB->exec_SELECTquery('*', 'tt_board', $where, '', $this->orderBy($type!='next'?'':'DESC'));
		$rc = $TYPO3_DB->sql_fetch_assoc($res);
		$TYPO3_DB->sql_free_result($res);
		return $rc;
	}

	/**
	 * Returns a message, formatted
	 */
	function outMessage ($string,$content='')	{
		$msg= '
		<hr>
		<h3>'.$string.'</h3>
		'.$content.'
		<hr>
		';

		return $msg;
	}

	/**
	 * Returns ORDER BY field
	 */
	function orderBy ($desc='')	{
		$rc = 'crdate '.$desc;
		return $rc;
	}

	/**
	 * Returns recent date from a tt_board record
	 */
	function recentDate ($rec)	{
		return $rec['tstamp'];
	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tt_board/lib/class.tx_ttboard_pibase.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tt_board/lib/class.tx_ttboard_pibase.php']);
}

?>
