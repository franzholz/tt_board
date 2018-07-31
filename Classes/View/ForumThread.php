<?php

namespace JambageCom\TtBoard\View;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * Function library for a forum thread
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TtBoard\Domain\Composite;

use JambageCom\Div2007\Utility\FrontendUtility;

class ForumThread implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
    * Creates the forum display, including listing all items/a single item
    */
    public function printView (
        Composite $composite,
        $treeView,
        $conf,
        $ref,
        $theCode,
        $linkParams,
        $pid
    )
    {
        $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();
        $controlObj = GeneralUtility::makeInstance('JambageCom\Div2007\Utility\ControlUtility');
        $recentPosts = array();
        $languageObj = $composite->getLanguageObj();
        $markerObj = $composite->getMarkerObj();
        $modelObj = $composite->getModelObj();
        $uid = $composite->getTtBoardUid();
        $pid_list = $composite->getPidList();
        $orig_templateCode = $composite->getOrigTemplateCode();
        $alternativeLayouts = $composite->getAlternativeLayouts();
        $prefixId = $composite->getPrefixId();
        $typolinkConf = $composite->getTypolinkConf();
        $allowCaching = $composite->getAllowCaching();
        $typolinkConf['useCacheHash'] = $allowCaching;
        $templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);

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

            if (
                version_compare(TYPO3_version, '8.7.0', '<')
            ) {
                $templateCode =
                    $local_cObj->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartMarkerArray,
                        $wrappedSubpartArray
                    );
            } else {
                $templateCode =
                    $templateService->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartMarkerArray,
                        $wrappedSubpartArray
                    );
            }

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
                        FrontendUtility::getTypoLink_URL(
                            $local_cObj,
                            $pid,
                            $linkParams,
                            '',
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
                $url = FrontendUtility::getTypoLink_URL(
                    $local_cObj,
                    $pid,
                    $linkParams,
                    '',
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

            if (
                version_compare(TYPO3_version, '8.7.0', '<')
            ) {
                    // Substitute:
                $templateCode =
                    $local_cObj->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartArray,
                        $wrappedSubpartArray
                    );
            } else {
                    // Substitute:
                $templateCode =
                    $templateService->substituteMarkerArrayCached(
                        $templateCode,
                        $markerArray,
                        $subpartArray,
                        $wrappedSubpartArray
                    );
            }

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
                    FrontendUtility::getTypoLink_URL(
                        $local_cObj,
                        $pid,
                        $linkParams,
                        '',
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
                            FrontendUtility::getTypoLink_URL(
                                $local_cObj,
                                $pid,
                                $linkParams,
                                '',
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

                if (
                    version_compare(TYPO3_version, '8.7.0', '<')
                ) {
                        // Substitute:
                    $subpartContent .=
                        $local_cObj->substituteMarkerArrayCached(
                            $out,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartArray
                        );
                } else {
                        // Substitute:
                    $subpartContent .=
                        $templateService->substituteMarkerArrayCached(
                            $out,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartArray
                        );
                }
            }

            $GLOBALS['TSFE']->indexedDocTitle = $indexedTitle;
                // Substitution:
            $content .=
                $templateService->substituteSubpart(
                    $templateCode,
                    '###CONTENT###',
                    $subpartContent
                );
        } else {
            debug('No template subpart for thread view: ###TEMPLATE_THREAD###'); // keep this
        }

        return $content;
    } // printView
}

