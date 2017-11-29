<?php

namespace JambageCom\TtBoard\View;

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
 *
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


class Forum implements \TYPO3\CMS\Core\SingletonInterface {

    /**
    * Creates the forum display, including listing all items/a single item
    */
    public function printView (
        $languageObj,
        $markerObj,
        $modelObj,
        $treeView,
        $conf,
        $uid,
        $ref,
        $pid_list,
        $theCode,
        $orig_templateCode,
        $alternativeLayouts,
        $linkParams,
        $prefixId,
        $pid,
        $typolinkConf,
        $allowCaching
    ) {
        $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();
        $controlObj = GeneralUtility::makeInstance('JambageCom\Div2007\Utility\ControlUtility');
        $recentPosts = array();
        $searchWord = $controlObj->readGP('sword', $prefixId);
        $pointerName = 'pointer';

        if (
            (
                $uid ||
                $ref != ''
            ) &&
            $theCode == 'FORUM'
        ) {
            if (!$allowCaching) {
                $GLOBALS['TSFE']->set_no_cache();	// MUST set no_cache as this displays single items and not a whole page....
            }
            $lConf = $conf['view_thread.'];
            $templateCode =
                $local_cObj->getSubpart(
                    $orig_templateCode,
                    '###TEMPLATE_THREAD###'
                );

            if ($templateCode) {

                    // Clear
                $subpartMarkerArray = array();
                $wrappedSubpartArray = array();

                    // Getting the specific parts of the template
                $markerObj->getColumnMarkers(
                    $markerArray,
                    $languageObj
                );
                $templateCode =
                    $local_cObj->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartMarkerArray,
                        $wrappedSubpartArray
                    );

                $rootParent = $modelObj->getRootParent($uid, $ref);
                $theadRootUid = $uid;
                $crdate = 0;
                if (
                    is_array($rootParent) &&
                    $rootParent['uid']
                ) {
                    $theadRootUid = $rootParent['uid'];
                    $crdate = $rootParent['crdate'];
                } else {
                    $row =
                        $modelObj->getCurrentPost(
                            $uid,
                            $ref
                        );
                    if (
                        is_array($row)
                    ) {
                        $crdate = $row['crdate'];
                    }
                }
                $wholeThread = $modelObj->getSingleThread($theadRootUid, $ref, 1);

                if (is_object($treeView)) {
                    $treeView->addTreeIcons($wholeThread);
                }

                if ($lConf['single']) {
                    foreach ($wholeThread as $recentP) {
                        if ($recentP['uid'] == $uid) {
                            $recentPosts[] = $recentP;
                            break;
                        }
                    }
                } else {
                    $recentPosts = $wholeThread;
                }
                $thread = array();
                $thread['next'] = $modelObj->getThreadRoot($pid_list, $crdate);
                $thread['previous'] = $modelObj->getThreadRoot($pid_list, $crdate, 'prev');
                $subpartContent = '';

                    // Clear
                $markerArray = array();
                $subpartArray = array();
                $wrappedSubpartArray = array();

                    // Getting the specific parts of the template
                $markerArray['###FORUM_TITLE###'] =
                    $local_cObj->stdWrap(
                        $GLOBALS['TSFE']->page['title'],
                        $lConf['forum_title_stdWrap.']
                    );

                    // Link back to forum
                $local_cObj->setCurrentVal($pid);
                $wrappedSubpartArray['###LINK_BACK_TO_FORUM###'] =
                    $local_cObj->typolinkWrap(
                        $typolinkConf
                    );

                $destinations = array('prev' /* previous */, 'next');
                foreach ($destinations as $destination) {
                    $destinationUid = 0;
                    if (
                        is_array($thread[$destination]) &&
                        !empty($thread[$destination]['uid'])
                    ) {
                        $destinationUid = $thread[$destination]['uid'];
                    }

                    if ($destinationUid) {
                            // Link to previous or next thread
                        $linkParams[$prefixId . '[uid]'] = $destinationUid;
                        $url =
                            \tx_div2007_alpha5::getPageLink_fh003(
                                $local_cObj,
                                $pid,
                                '',
                                $linkParams,
                                array(
                                    'useCacheHash' => $allowCaching
                                )
                            );
                        $wrappedSubpartArray['###LINK_' . strtoupper($destination) . '_THREAD###'] =
                            array(
                                '<a href="' . htmlspecialchars($url) . '">',
                                '</a>'
                            );
                    } else {
                        $subpartArray['###LINK_' . strtoupper($destination) . '_THREAD###'] = '';
                    }
                }

                if (is_array($rootParent)) {
                        // Link to first !!
                    $linkParams[$prefixId . '[uid]' ] = $rootParent['uid'];
                    $url = \tx_div2007_alpha5::getPageLink_fh003(
                        $local_cObj,
                        $pid,
                        '',
                        $linkParams,
                        array('useCacheHash' => $allowCaching)
                    );

                    $wrappedSubpartArray['###LINK_FIRST_POST###'] =
                        array(
                            '<a href="' . htmlspecialchars($url) . '">',
                            '</a>'
                        );
                } else {
                    $subpartArray['###LINK_FIRST_POST###'] = '';
                }

                    // Substitute:
                $templateCode =
                    $local_cObj->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartArray,
                        $wrappedSubpartArray
                    );

                    // Getting subpart for items:
                $postHeader =
                    $markerObj->getLayouts(
                        $templateCode,
                        $alternativeLayouts,
                        'POST'
                    );
                $c_post = 0;
                $indexedTitle = '';

                foreach ($recentPosts as $recentPost) {
                    $out = $postHeader[$c_post % count($postHeader)];
                    $c_post++;
                    if (
                        !$indexedTitle &&
                        trim($recentPost['subject'])
                    ) {
                        $indexedTitle = trim($recentPost['subject']);
                    }

                        // Clear
                    $markerArray = array();
                    $subpartMarkerArray = array();
                    $wrappedSubpartArray = array();

                    $markerObj->getRowMarkerArray(
                        $markerArray,
                        $modelObj,
                        $recentPost,
                        'POST',
                        $lConf
                    );

                        // Link to the post
                    $linkParams[$prefixId . '[uid]'] = $recentPost['uid'];
                    $url =
                        \tx_div2007_alpha5::getPageLink_fh003(
                            $local_cObj,
                            $pid,
                            '',
                            $linkParams,
                            array('useCacheHash' => $allowCaching)
                        );

                    $wrappedSubpartArray['###LINK###'] =
                        array(
                            '<a href="' . htmlspecialchars($url) . '">',
                            '</a>'
                        );

                    foreach ($destinations as $destination) {

                        $destinationUid = 0;
                        if (!empty($recentPost[$destination . 'Uid'])) {
                            $destinationUid = $recentPost[$destination . 'Uid'];
                        } else if (
                            is_array($thread[$destination]) &&
                            !empty($thread[$destination]['uid'])
                        ) {
                            $destinationUid = $thread[$destination]['uid'];
                        }

                        if ($destinationUid) {
                                // Link to the previous or next thread
                            $linkParams[$prefixId . '[uid]'] = $destinationUid;

                            $url =
                                \tx_div2007_alpha5::getPageLink_fh003(
                                    $local_cObj,
                                    $pid,
                                    '',
                                    $linkParams,
                                    array('useCacheHash' => $allowCaching)
                                );

                            $wrappedSubpartArray['###LINK_' . strtoupper($destination) . '_POST###'] =
                                array(
                                    '<a href="' .  htmlspecialchars($url) . '">',
                                    '</a>'
                                );
                        } else {
                            $subpartMarkerArray['###LINK_' . strtoupper($destination) . '_POST###'] = '';
                        }
                    }

                        // Substitute:
                    $subpartContent .=
                        $local_cObj->substituteMarkerArrayCached(
                            $out,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartArray
                        );
                }

                $GLOBALS['TSFE']->indexedDocTitle = $indexedTitle;
                    // Substitution:
                $content .=
                    $local_cObj->substituteSubpart(
                        $templateCode,
                        '###CONTENT###',
                        $subpartContent
                    );
            } else {
                debug('No template subpart for thread view: ###TEMPLATE_THREAD###');
            }
        } else { // if ($this->tt_board_uid && $theCode == 'FORUM')
            $continue = true;
            if ($theCode == 'THREAD_TREE') {

                if (!$uid && $ref == '') {
                    $continue = false;
                }

                $lConf = $conf['thread_tree.'];
            } else {
                $lConf = $conf['list_threads.'];
            }

            $limit = $lConf['thread_limit'];

            if ($continue) {
                    // Clear
                $subpartMarkerArray = array();
                $wrappedSubpartArray = array();

                $templateCode =
                    $local_cObj->getSubpart(
                        $orig_templateCode,
                        '###LINK_BROWSE###'
                    );

                $browserConf = '';
                if (
                    isset($lConf['browser']) &&
                    $lConf['browser'] == 'div2007'
                ) {
                    if ($templateCode) {
                        $browserConf = array();
                        if (isset($lConf['browser.'])) {
                            $browserConf = $lConf['browser.'];
                        }
                    }

                    $addQueryString = array();
                    $recordCount = 0;
                    $more = 0;
                    $useCache = true;

                    if ($theCode == 'FORUM') {
                        $recordCount = $modelObj->getNumThreads(
                            $pid_list,
                            $ref,
                            $searchWord
                        );
                    }

                    if ($recordCount > $limit) {
                        $more = 1;
                    }
                    if ($searchWord != '') {
                        $addQueryString['sword'] = $searchWord;
                        $useCache = false;
                    }

                    $begin_at = intval($controlObj->readGP($pointerName, $prefixId)) * $limit;
                    $piVars = $controlObj->readGP('', $prefixId);

                    $markerObj->getBrowserMarkers(
                        $markerArray,
                        $subpartMarkerArray,
                        $wrappedSubpartArray,
                        $local_cObj,
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
                    );
                }

                $templateCode =
                    $local_cObj->getSubpart(
                        $orig_templateCode,
                        '###TEMPLATE_FORUM###'
                    );

                if ($templateCode) {
                        // Getting the specific parts of the template
                    $markerObj->getColumnMarkers(
                        $markerArray,
                        $languageObj
                    );

                    $markerArray['###FORUM_TITLE###'] =
                        $local_cObj->stdWrap(
                            $GLOBALS['TSFE']->page['title'],
                            $lConf['forum_title_stdWrap.']
                        );
                    $templateCode =
                        $local_cObj->substituteMarkerArrayCached(
                            $templateCode,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartArray
                        );
                    $postHeader =
                        $markerObj->getLayouts(
                            $templateCode,
                            $alternativeLayouts,
                            'POST'
                        );
                        // Template code used if tt_board_uid matches...
                    $postHeader_active =
                        $markerObj->getLayouts(
                            $templateCode,
                            1,
                            'POST_ACTIVE'
                        );
                    $subpartContent = '';

                    if (
                        $theCode == 'THREAD_TREE' &&
                        $uid > 0
                    ) {
                        $parentUid = $uid;
                        $rootParent =
                            $modelObj->getRootParent(
                                $uid,
                                $ref
                            );
                        if (is_array($rootParent)) {
                            $parentUid = $rootParent['uid'];
                        }

                        $recentPosts =
                            $modelObj->getSingleThread(
                                $parentUid,
                                $ref,
                                1
                            );
                    } else {
                        $recentPosts =
                            $modelObj->getThreads(
                                $pid_list,
                                $ref,
                                $conf['tree'],
                                $lConf['thread_limit'] ?
                                    $lConf['thread_limit'] :
                                    '50',
                                $begin_at,
                                $controlObj->readGP('sword', $prefixId)
                            );
                    }
                    if (is_object($treeView)) {
                        $treeView->addTreeIcons($recentPosts);
                    }

                    $c_post = 0;
                    $subpartArray = array();

                    foreach ($recentPosts as $recentPost) {
                        $out = $postHeader[$c_post % count($postHeader)];

                        if ($recentPost['uid'] == $uid && $postHeader_active[0]) {
                            $out = $postHeader_active[0];
                        }
                        $c_post++;
                        $local_cObj->start($recentPost);

                            // Clear
                        $markerArray = array();
                        $wrappedSubpartArray = array();

                            // Markers
                        $markerObj->getRowMarkerArray(
                            $markerArray,
                            $modelObj,
                            $recentPost,
                            'POST',
                            $lConf
                        );

                            // Link to the post
                        $overrulePIvars = array(
                            'uid' => ($recentPost['uid'])
                        );

                        $linkParams[$prefixId . '[uid]'] = $recentPost['uid'];
                        $url =
                            \tx_div2007_alpha5::getPageLink_fh003(
                                $local_cObj,
                                $pid,
                                '',
                                $linkParams,
                                array('useCacheHash' => $allowCaching)
                        );
                        $wrappedSubpartArray['###LINK###'] =
                            array(
                                '<a href="' . htmlspecialchars($url)  . '">',
                                '</a>'
                        );

                            // Last post processing:
                        $lastPostInfo =
                            $modelObj->getLastPostInThread(
                                $recentPost['pid'],
                                $recentPost['uid'],
                                $ref
                            );
                        if (!$lastPostInfo) {
                            $lastPostInfo = $recentPost;
                        }

                        $local_cObj->start($lastPostInfo);
                        $recentDate = $modelObj->recentDate($lastPostInfo);
                        $markerArray['###LAST_POST_DATE###'] =
                            $local_cObj->stdWrap(
                                $recentDate,
                                $conf['date_stdWrap.']
                            );
                        $markerArray['###LAST_POST_TIME###'] =
                            $local_cObj->stdWrap(
                                $recentDate,
                                $conf['time_stdWrap.']
                            );
                        $markerArray['###LAST_POST_AGE###'] =
                            $local_cObj->stdWrap(
                                $recentDate,
                                $conf['age_stdWrap.']
                            );
                        $markerArray['###LAST_POST_AUTHOR###'] =
                            $local_cObj->stdWrap(
                                $markerObj->formatStr($lastPostInfo['author']),
                                $lConf['last_post_author_stdWrap.']
                            );

                            // Link to the last post
                        $linkParams[$prefixId . '[uid]'] = $lastPostInfo['uid'];
                        $url =
                            \tx_div2007_alpha5::getPageLink_fh003(
                                $local_cObj,
                                $pid,
                                '',
                                $linkParams,
                                array('useCacheHash' => $allowCaching)
                            );
                        $wrappedSubpartArray['###LINK_LAST_POST###'] =
                            array(
                                '<a href="' .  htmlspecialchars($url) . '">',
                                '</a>'
                            );

                            // Substitute:
                        $subpartArray[$recentDate . sprintf('%010d', $recentPost['uid'])] =
                            $local_cObj->substituteMarkerArrayCached(
                                $out,
                                $markerArray,
                                array(),
                                $wrappedSubpartArray
                            );
                    }

                    if (!$conf['tree']) {
                        krsort($subpartArray);
                    }

                        // Substitution:
                    $markerArray = array();
                    $subpartContentArray = array();
                    $markerArray['###SEARCH_NAME###'] = $prefixId . '[sword]';

                        // Fill in array
                    $markerArray['###SEARCH_WORD###'] =
                        $GLOBALS['TSFE']->no_cache ?
                            $controlObj->readGP('sword', $prefixId) :
                            '';	// Setting search words in field if cache is disabled.
                        // Set FORM_URL
                    $local_cObj->setCurrentVal($GLOBALS['TSFE']->id);
                    $temp_conf = $typolinkConf;
                    $temp_conf['no_cache'] = 1;
                    $markerArray['###FORM_URL###'] = $local_cObj->typoLink_URL($temp_conf);
                    $subpartContent = implode('', $subpartArray);

                    // Substitute CONTENT-subpart
                    $subpartContentArray['###CONTENT###'] = $subpartContent;
                    $newContent = $local_cObj->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartContentArray
                    );

                    $content .= $newContent;
                } // if ($templateCode) {
            } // if($continue) {
        }

        return $content;
    } // printView
}

