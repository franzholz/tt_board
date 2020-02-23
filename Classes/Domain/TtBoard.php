<?php

namespace JambageCom\TtBoard\Domain;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Kasper Skårhøj <kasperYYYY@typo3.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
// *  the Free Software Foundation; either version 2 of the License, or
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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TtBoard\Constants\TreeMark;
use JambageCom\TtBoard\Domain\QueryParameter;


class TtBoard implements \TYPO3\CMS\Core\SingletonInterface
{
    public $enableFields = '';		// The enablefields of the tt_board table.
    public $searchFieldList = 'author,email,subject,message';
    protected $tablename = 'tt_board';


    public function init ()
    {
        $enableFields = \JambageCom\Div2007\Utility\TableUtility::enableFields($this->tablename);
        $this->setEnableFields($enableFields);
    }

    public function getTablename ()
    {
        return $this->tablename;
    }

    public function getQueryBuilder ()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->getTablename());
        return $queryBuilder;
    }

    public function setEnableFields ($value)
    {
        $this->enableFields = $value;
    }

    public function getEnableFields ()
    {
        return $this->enableFields;
    }

    /**
     * Returns the reference to an external table to which the forum belongs
    *  @return	QueryParameter ... $andWhereEqualsArray array of QueryParameter
     */
    public function getWhereRef ($ref)
    {
        $result = null;

        if ($ref != '') {
            $result =
                GeneralUtility::makeInstance(
                    QueryParameter::class,
                    QueryParameter::CLAUSE_AND_WHERE,
                    $this->getTablename(),
                    'reference',
                    $ref, 
                    \PDO::PARAM_STR,
                    QueryParameter::COMP_EQUAL
                );
        }
        return $result;
    }

    /**
    * Checks if posting is allowed to user
    */
    static public function isAllowed ($memberOfGroups)
    {
        $allowed = false;

        if (
            $memberOfGroups != '' &&
            $memberOfGroups != '{$plugin.tt_board.memberOfGroups}' &&
            $memberOfGroups != '0'
        ) {
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
    static public function getPagesInPage ($pid_list)
    {
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
                !isset($GLOBALS['TYPO3_CONF_VARS']['FE']['content_doktypes']) || // removed since TYPO3 9.5
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
     * @param string ... $pidList comma separated list of page ids.
     * @param array  ... $andWhereEqualsArray array of QueryParameter for equation comparisons
     */
    public function getNumPosts ($pidList, array $queryParameters = [])
    {
        $pageIds =  GeneralUtility::intExplode(',', $pidList, true);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));

        $field = 'pid';
        if ($queryParameter->tablename != '') {
            $field = $queryParameter->tablename . '.' . $field;
        }

        $queryBuilder
            ->count('*')
            ->from($this->getTablename())
            ->where(
                $queryBuilder->expr()->in(
                    $field,
                    $queryBuilder->createNamedParameter(
                        $pageIds,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );

        $whereCount = 0;
        foreach ($queryParameters as $queryParameter) {
            if (empty($queryParameter)) {
                continue;
            }

            if (
                !is_object($queryParameter) ||
                !$queryParameter instanceof QueryParameter
            ) {
                throw new \RuntimeException(TT_BOARD_EXT . ': wrong query parameter type "' . get_class($queryParameter) . '"');
            }

            switch ($queryParameter->clause) {
                case QueryParameter::CLAUSE_AND_WHERE:

                        $parameter =
                            $queryBuilder->createNamedParameter(
                                $queryParameter->value,
                                $queryParameter->type
                            );
                        $expression = '';

                        switch ($queryParameter->comparator) {
                            case QueryParameter::COMP_EQUAL:
                                $field = $queryParameter->field;
                                if ($queryParameter->tablename != '') {
                                    $field = $queryParameter->tablename . '.' . $field;
                                }
                                $expression = $queryBuilder->expr()->eq(
                                    $field,
                                    $parameter
                                );
                                break;
                            default:
                                throw new \RuntimeException(TT_BOARD_EXT . ': wrong comparator in parameter field "' . $queryParameter->field . '"');
                            break;
                        }

                        if ($whereCount) {
                            $queryBuilder->andWhere(
                                $expression
                            );
                        } else {
                            $whereCount++;
                            $queryBuilder->where(
                                $expression
                            );
                        }
                    break;
                default:
                    break;
            }
        }

        $result =
            $queryBuilder
                ->execute()
                ->fetchColumn(0);

        return $result;
    }

    /**
    * Returns number of threads.
     * @param string   ... $pid page id
    *  @param array    ... $andWhereEqualsArray array of QueryParameter
     */
    public function getNumThreads ($pid, $ref = '', $searchWord = 0)
    {
        $count = 0;
        $whereRef = $this->getWhereRef($ref);
        $queryParameters = [];
        $queryParameters[] = $whereRef;

        if ($searchWord) {
            $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
            $where =
                $local_cObj->searchWhere(
                    $searchWord,
                    $this->searchFieldList,
                    $this->getTablename()
                );
            // TODO $where
            $count = $this->getNumPosts($pid, $whereRef . $where);
        } else {
            $queryParameter =
                GeneralUtility::makeInstance(
                    QueryParameter::class,
                    QueryParameter::CLAUSE_AND_WHERE,
                    $this->getTablename(),
                    'parent', 
                    0,
                    \PDO::PARAM_INT,
                    QueryParameter::COMP_EQUAL
                );
            $queryParameters[] = $queryParameter;
            $count = $this->getNumPosts($pid, $queryParameters);
        }

        return $count;
    }

    /**
    * Returns number of replies.
    */
    public function getNumReplies ($pid, $uid)
    {
        $queryParameters = [];
        $queryParameter =
            GeneralUtility::makeInstance(
                QueryParameter::class,
                QueryParameter::CLAUSE_AND_WHERE,
                $this->getTablename(),
                'parent',
                intval($uid), \PDO::PARAM_INT,
                QueryParameter::COMP_EQUAL
            );
        $queryParameters[] = $queryParameter;
        $count = $this->getNumPosts($pid, $queryParameters);

        return $count;
    }

    /**
    * Returns last post.
    */
    public function getLastPost ($pidList)
    {
        $pageIds =  GeneralUtility::intExplode(',', $pidList, true);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));
        $field = 'pid';

        $result = $queryBuilder
            ->select('*')
            ->from($this->getTablename())
            ->where(
                $queryBuilder->expr()->in(
                    $field,
                    $queryBuilder->createNamedParameter(
                        $pageIds,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->orderBy('DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return $result;
    }

    /**
    * Returns last post in thread.
    */
    public function getLastPostInThread ($pidList, $uid, $ref)
    {
        $pageIds =  GeneralUtility::intExplode(',', $pidList, true);    
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));
        $field = 'pid';
        $whereRef = $this->getWhereRef($ref);

        $result = $queryBuilder
            ->select('*')
            ->from($this->getTablename())
            ->where(
                $queryBuilder->expr()->in(
                    $field,
                    $queryBuilder->createNamedParameter(
                        $pageIds,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'parent',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            );

        if (!empty($whereRef)) {
            $field = $whereRef->field;
            if ($whereRef->tablename != '') {
                $field = $whereRef->tablename . '.' . $field;
            }
            $expression = $queryBuilder->expr()->eq(
                $field,
                $parameter
            );
            $queryBuilder->andWhere(
                $expression
            );
        }

        $result = $queryBuilder
            ->orderBy('crdate', 'DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return $result;
    }

    /**
    * Returns last post in thread.
    */
    public function getCurrentPost ($uid, $ref)
    {
        $result = false;
        if ($uid || $ref != '') {
            $whereUid = '1=1';
            if ($uid) {
                $whereUid = 'uid=' . intval($uid);
            }
            $whereRef = $this->getWhereRef($ref);
            $where = $whereUid . $whereRef . $this->getEnableFields();

            $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                '*',
                $this->getTablename(),
                $where,
                '',
                $this->orderBy('DESC')
            );
            $result = $row;
        }
        return $result;
    }

    /**
    * Most recent posts.
    *
    * Returns an array with records
    */
    public function getMostRecentPosts ($pid, $number, $days = 300)
    {
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
    public function getRootParent ($uid, $ref = '', $limit = 99, $calllevel = 0)
    {
        $result = false;
        $error = false;

        if ($uid) {
            $field = 'uid';
            $value = $uid;
        } else if ($ref != '') {
            $field = 'reference';
            $value = $ref;
        } else {
            return false;
        }

        if (
            $limit > 0
        ) {
            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    $this->getTablename(),
                    $field . '=' .
                        $GLOBALS['TYPO3_DB']->fullQuoteStr(
                            $value,
                            $this->getTablename()
                        ) .
                        $this->getEnableFields()
                );

            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if ($row['parent']) {
                    $tmpRow =
                        $this->getRootParent(
                            $row['parent'],
                            '',
                            $limit - 1,
                            $calllevel + 1
                        );

                    if ($tmpRow) {
                        $result = $tmpRow;
                    }
                } else if (
                    $calllevel > 0 ||
                    $ref != ''
                ) {
                    $result = $row;
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $result;
    }

    /**
    * Returns next or prev thread in a tree
    */
    public function getThreadRoot ($pid, $crdate, $type = 'next')
    {
        $datePart = ' AND crdate' . ($type != 'next' ? '>' : '<') . intval($crdate);
        $where = 'pid IN (' . $pid . ') AND parent=0' . $datePart . $this->getEnableFields();
        $res =
            $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                $this->getTablename(),
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
    )
    {
        $outArray = array();
        if ($uid) {
            $hash = md5($uid . '|' . $ref . '|' . $descend);
            if ($this->cache_thread[$hash]) {
                return $this->cache_thread[$hash];
            }

            $whereUid = 'uid=' . intval($uid);
            $whereRef = $this->getWhereRef($ref);

            $res =
                $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    $this->getTablename(),
                    $whereUid . $whereRef . $this->getEnableFields()
                );

            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

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
    * Returns an array with threads
    */
    public function getThreads (
        $pid,
        $ref,
        $descend = 0,
        $limit = 100,
        $offset = 0,
        $searchWord = 0
    )
    {
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
            $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
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
                if (!$rootRow) {
                    $rootRow = $row;
                }

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
                    $this->getTablename(),
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
    public function getRecordTree (
        &$theRows,
        $parent,
        $pid,
        $ref,
        $treeMarks = ''
    )
    {
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
    static public function orderBy ($desc = '')
    {
        $result = 'crdate ' . $desc;
        return $result;
    }

    /**
    * Returns recent date from a tt_board record
    */
    static public function recentDate ($row)
    {
        return $row['tstamp'];
    }
}


