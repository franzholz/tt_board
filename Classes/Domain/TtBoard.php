<?php

namespace JambageCom\TtBoard\Domain;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * Function library for a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TtBoard\Constants\TreeMark;


class TtBoard implements \TYPO3\CMS\Core\SingletonInterface {

    public $enableFields = '';		// The enablefields of the tt_board table.
    public $searchFieldList = 'author,email,subject,message';
    protected $tablename = 'tt_board';


    public function init () {
        $enableFields = \JambageCom\Div2007\Utility\TableUtility::enableFields($this->tablename);
        $this->setEnableFields($enableFields);
    }

    public function getTablename () {
        return $this->tablename;
    }

    public function setEnableFields ($value) {
        $this->enableFields = $value;
    }

    public function getEnableFields () {
        return $this->enableFields;
    }

    static public function getWhereRef ($ref) {
        $result = '';

        if ($ref != '') {
            $result = ' AND reference=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($ref, $this->getTablename());
        }
        return $result;
    }


    /**
    * Checks if posting is allowed to user
    */
    static public function isAllowed ($memberOfGroups) {
        $allowed = false;

        if ($memberOfGroups) {
            if (is_array($GLOBALS['TSFE']->fe_user->user)) {
                $requestGroupArray =
                    GeneralUtility::trimExplode(
                        ',',
                        $memberOfGroups
                    );
                $usergroupArray = explode(',', $GLOBALS['TSFE']->fe_user->user['usergroup']);
                $fitArray = array_intersect($requestGroupArray, $usergroupArray);
                if (count($fitArray)) {
                    $allowed = true;
                }
            }
        } else {
            $allowed = true;
        }
        return $allowed;
    }


    /**
    * Get subpages
    *
    * This function returns an array a pagerecords from the page-uid's in the pid_list supplied.
    * Excludes pages, that would normally not enter a regular menu. That means hidden, timed or deleted pages and pages with another doktype than 'standard' or 'advanced'
    */
    static public function getPagesInPage ($pid_list) {
        $result = array();
        $thePids = GeneralUtility::intExplode(',', $pid_list);
        $pageRows = array();
        foreach($thePids as $pid) {
            $menuRows = $GLOBALS['TSFE']->sys_page->getMenu($pid);
                // avoid the insertion of duplicate page rows
            foreach ($menuRows as $menuRow) {
                $uid = $menuRow['uid'];
                if (!isset($pageRows[$uid])) {
                    $pageRows[$uid] = $menuRow;
                }
            }
        }

            // Exclude pages not of doktype 'Standard' or 'Advanced'
        foreach($pageRows as $pageRow) {
            if (
                GeneralUtility::inList(
                    $GLOBALS['TYPO3_CONF_VARS']['FE']['content_doktypes'],
                    $pageRow['doktype']
                )
            ) {
                $result[] = $pageRow;
            } // All pages including pages 'not in menu'
        }
        return $result;
    }


    /**
    * Returns number of post in a forum.
    */
    public function getNumPosts ($pid, $where = '') {
        $where = 'pid IN (' . $pid . ')' . $where . $this->getEnableFields();
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('*', $this->getTablename(), $where);

        return $result;
    }


    /**
    * Returns number of threads.
    */
    public function getNumThreads ($pid, $ref = '', $searchWord = 0) {
        $count = 0;
        $whereRef = $this->getWhereRef($ref);

        if ($searchWord) {
            $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();
            $where =
                $local_cObj->searchWhere(
                    $searchWord,
                    $this->searchFieldList,
                    $this->getTablename()
                );
            $count = $this->getNumPosts ($pid, $whereRef . $where);
        } else {
            $count = $this->getNumPosts ($pid, ' AND parent=0' . $whereRef);
        }

        return $count;
    }



    /**
    * Returns number of replies.
    */
    public function getNumReplies ($pid, $uid) {
        $count = $this->getNumPosts ($pid, ' AND parent=' . intval($uid));

        return $count;
    }


    /**
    * Returns last post.
    */
    public function getLastPost ($pid) {
        $where = 'pid IN (' . $pid . ')' . $this->getEnableFields();
        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            '*',
            $this->getTablename(),
            $where,
            '',
            $this->orderBy('DESC')
        );

        return $row;
    }


    /**
    * Returns last post in thread.
    */
    public function getLastPostInThread ($pid, $uid, $ref) {
        $whereRef = $this->getWhereRef($ref);
        $where = 'pid IN (' . $pid . ') AND parent=' . $uid . $whereRef . $this->getEnableFields();

        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            '*',
            $this->getTablename(),
            $where,
            '',
            $this->orderBy('DESC')
        );
        return $row;
    }


    /**
    * Most recent posts.
    *
    * Returns an array with records
    */
    public function getMostRecentPosts ($pid, $number, $days = 300) {

        $timeWhere = '';

        if ($days) {
            $temptime = time() - 86400 * intval(trim($days));
            $timeWhere = ' AND crdate >= ' . $temptime;
        }

        $where = 'pid IN (' . $pid . ')' . $timeWhere . $this->getEnableFields();

        $result =
            $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                $this->getTablename(),
                $where,
                '',
                $this->orderBy('DESC'),
                $number
            );

        return $result;
    }


    /**
    * Get root parent of a tt_board record by uid or reference.
    */
    public function getRootParent ($uid, $ref = '', $limit = 99) {
        if ($uid) {
            $field = 'uid';
            $value = $uid;
        } else {
            $field = 'reference';
            $value = $ref;
        }

        if ($limit > 0) {
            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    'tt_board',
                    $field . '=' .
                        $GLOBALS['TYPO3_DB']->fullQuoteStr(
                            $value,
                            'tt_board'
                        ) .
                        $this->getEnableFields()
                );

            if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if ($row['parent']) {
                    $tmpRow =
                        $this->getRootParent(
                            $row['parent'],
                            '',
                            $limit - 1
                        );
                    if ($tmpRow) {
                        $row = $tmpRow;
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $row;
    }


    /**
    * Returns next or prev thread in a tree
    */
    public function getThreadRoot ($pid, $rootParent, $type = 'next') {
        $datePart = ' AND crdate' . ($type != 'next' ? '>' : '<') . intval($rootParent['crdate']);
        $where = 'pid IN (' . $pid . ') AND parent=0' . $datePart . $this->getEnableFields();
        $res =
            $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'tt_board',
                $where,
                '',
                $this->orderBy($type != 'next' ? '' : 'DESC')
            );
        $result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $result;
    }


    /**
    * Returns records in a thread
    */
    public function getSingleThread (
        $uid,
        $ref,
        $descend = 0
    ) {
        $hash = md5($uid . '|' . $ref . '|' . $descend);
        if ($this->cache_thread[$hash]) {
            return $this->cache_thread[$hash];
        }

        $outArray = array();
        if ($uid) {
            $whereRef = $this->getWhereRef($ref);

            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    'tt_board',
                    'uid=' . $uid . $whereRef . $this->getEnableFields()
                );

            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

                $outArray[$row['uid']] = $row;
                if ($descend) {
                    $this->getRecordTree($outArray, $row['uid'], $row['pid'], $ref);
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $outArray;
    }


    /**
    * Returns an array with threads
    */
    public function getThreads (
        $pid,
        $ref,
        $descend = 0,
        $limit = 100,
        $offset = 0,
        $searchWord = 0
    ) {
        $outArray = array();
        $whereRef = $this->getWhereRef($ref);
        $limitString = '';
        if ($limit) {
            $limitString = intval($limit);
            if ($offset) {
                $limitString = intval($offset) . ',' . $limitString;
            }
        }

        if ($searchWord) {
            $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();
            $where =
                $local_cObj->searchWhere(
                    $searchWord,
                    $this->searchFieldList,
                    $this->getTablename()
                );
            $where = 'pid IN (' . $pid . ')' . $whereRef . $where . $this->getEnableFields();
            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    $this->getTablename(),
                    $where,
                    '',
                    $this->orderBy('DESC'),
                    $limitString
                );
            $set = array();
            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $rootRow = $this->getRootParent($row['uid']);

                if (is_array($rootRow) && !isset($set[$rootRow['uid']])) {
                    $set[$rootRow['uid']] = 1;
                    $outArray[$rootRow['uid']] = $rootRow;

                    if ($descend) {
                        $this->getRecordTree(
                            $outArray,
                            $rootRow['uid'],
                            $rootRow['pid'],
                            $ref
                        );
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        } else {
            $where = 'pid IN (' . $pid . ') AND parent=0' . $whereRef . $this->getEnableFields();
            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    'tt_board',
                    $where,
                    '',
                    $this->orderBy('DESC'),
                    $limitString
                );
            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $outArray[$row['uid']] = $row;

                if ($descend) {
                    $this->getRecordTree(
                        $outArray,
                        $row['uid'],
                        $row['pid'],
                        $ref
                    );
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $outArray;
    }


    /**
    * Get a record tree of forum items
    */
    public function getRecordTree (&$theRows, $parent, $pid, $ref, $treeMarks = '') {

        if ($treeMarks != '') {
            $treeMarks .= ',';
        }
        $whereRef = $this->getWhereRef($ref);
        $where = 'pid=' . intval($pid) . ' AND parent=' . intval($parent) . $whereRef . $this->getEnableFields();

        $res =
            $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                $this->getTablename(),
                $where,
                '',
                $this->orderBy()
            );
        $counter = 0;
        $numberRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        $prevUid = end(array_keys($theRows));

        if ($theRows[$prevUid]['treeMarks'] != '') {
            $theRows[$prevUid]['treeMarks'] .= ',';
        }

        $theRows[$prevUid]['treeMarks'] .=
            (
                $numberRows ?
                TreeMark::THREAD :
                TreeMark::END
            );

        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $counter++;
            $uid = $row['uid'];

            // check for a loop
            if (isset($theRows[$uid])) {
                break;
            }

            $row['treeMarks'] =
                $treeMarks . (
                    $numberRows == $counter ?
                        TreeMark::JOIN_BOTTOM :
                        TreeMark::JOIN
                );

            // prev/next item:
            $theRows[$prevUid]['nextUid'] = $uid;
            $row['prevUid'] = $theRows[$prevUid]['uid'];
            $theRows[$uid] = $row;
                // get the branch
            $this->getRecordTree(
                $theRows,
                $uid,
                $row['pid'],
                $ref,
                $treeMarks . ($numberRows == $counter ? TreeMark::BLANK : TreeMark::LINE)
            );
            $prevUid = $uid;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
    }


    /**
    * Returns ORDER BY field
    */
    static public function orderBy ($desc = '') {
        $result = 'crdate ' . $desc;
        return $result;
    }


    /**
    * Returns recent date from a tt_board record
    */
    static public function recentDate ($rec) {
        return $rec['tstamp'];
    }
}

