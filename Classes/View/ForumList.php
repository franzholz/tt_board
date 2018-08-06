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
 * forum list view
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TtBoard\Domain\Composite;

use JambageCom\Div2007\Utility\FrontendUtility;


class ForumList implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
    * Creates a list of forums or categories depending on theCode
    */
    public function render (
        $theCode,
        Composite $composite,
        array $linkParams
    )
    {
        $conf = $composite->getConf();
        $modelObj = $composite->getModelObj();
        $markerObj = $composite->getMarkerObj();
        $languageObj = $composite->getLanguageObj();
        $alternativeLayouts = $composite->getAlternativeLayouts();
        $allowCaching = $composite->getAllowCaching();
        $templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);

        $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();
        $local_cObj->setCurrentVal($GLOBALS['TSFE']->id);
        $forum_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer(array(), $modelObj->getTablename());

        if (!$composite->getTtBoardUid()) {
            $forumlist = 0;     // set to true if this is a list of forums and not categories + forums

            if ($theCode == 'LIST_CATEGORIES') {
                    // Config if categories are listed.
                $lConf = $conf['list_categories.'];
            } else {
                $forumlist = 1;
                    // Config if forums are listed.
                $lConf = $conf['list_forums.'];
                $lConf['noForums'] = 0;
            }

            $GLOBALS['TSFE']->set_cache_timeout_default($lConf['cache_timeout'] ? intval($lConf['cache_timeout']) : 300);
            $templateCode =
                $templateService->getSubpart(
                    $composite->getOrigTemplateCode(),
                    '###TEMPLATE_OVERVIEW###'
                );

            if ($templateCode) {
                    // Clear
                $subpartMarkerArray = array();
                $wrappedSubpartContentArray = array();

                    // Getting the specific parts of the template
                $markerObj->getColumnMarkers(
                    $markerArray,
                    $languageObj
                );

                    // Getting the icon markers
                $markerObj->getIconMarkers(
                    $markerArray,
                    $conf['icon.']
                );

                if (
                    version_compare(TYPO3_version, '8.7.0', '<')
                ) {
                    $templateCode =
                        $local_cObj->substituteMarkerArrayCached(
                            $templateCode,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartContentArray
                        );
                } else {
                    $templateCode =
                        $templateService->substituteMarkerArrayCached(
                            $templateCode,
                            $markerArray,
                            $subpartMarkerArray,
                            $wrappedSubpartContentArray
                        );
                }

                    // Getting the specific parts of the template
                $categoryHeader =
                    $markerObj->getLayouts(
                        $templateCode,
                        $alternativeLayouts,
                        'CATEGORY'
                    );

                $forumHeader =
                    $markerObj->getLayouts(
                        $templateCode,
                        $alternativeLayouts,
                        'FORUM'
                    );

                $postHeader =
                    $markerObj->getLayouts(
                        $templateCode,
                        $alternativeLayouts,
                        'POST'
                    );
                $subpartContent = '';

                    // Getting categories
                $categories = $modelObj->getPagesInPage($composite->getPidList());
                $c_cat = 0;

                foreach ($categories as $k => $catData) {
                        // Getting forums in category
                    if ($forumlist) {
                        $forums = $categories;
                    } else {
                        $forums = $modelObj->getPagesInPage($catData['uid']);
                    }

                    if (!$forumlist && count($categoryHeader)) {
                            // Rendering category
                        $out = $categoryHeader[$c_cat % count($categoryHeader)];
                        $c_cat++;
                        $local_cObj->start($catData);

                            // Clear
                        $markerArray = array();
                        $wrappedSubpartContentArray = array();

                            // Markers
                        $markerArray['###CATEGORY_TITLE###'] =
                            $local_cObj->stdWrap(
                                $markerObj->formatStr(
                                    $catData['title']
                                ),
                                $lConf['title_stdWrap.']
                            );

                        $markerArray['###CATEGORY_DESCRIPTION###'] =
                            $local_cObj->stdWrap(
                                $markerObj->formatStr(
                                    $catData['subtitle']
                                ),
                                $lConf['subtitle_stdWrap.']
                            );

                        $markerArray['###CATEGORY_FORUMNUMBER###'] =
                            $local_cObj->stdWrap(
                                count($forums),
                                $lConf['count_stdWrap.']
                            );

                        $pageLink =
                            FrontendUtility::getTypoLink_URL(
                                $composite->getCObj(),
                                $catData['uid'],
                                $linkParams,
                                '',
                                array('useCacheHash' => $allowCaching)
                            );

                        $wrappedSubpartContentArray['###LINK###'] =
                            array('<a href="' . htmlspecialchars($pageLink) . '">',
                            '</a>'
                        );

                        if (
                            version_compare(TYPO3_version, '8.7.0', '<')
                        ) {
                                // Substitute
                            $subpartContent .=
                                $local_cObj->substituteMarkerArrayCached(
                                    $out,
                                    $markerArray,
                                    array(),
                                    $wrappedSubpartContentArray
                                );
                        } else {
                                // Substitute
                            $subpartContent .=
                                $templateService->substituteMarkerArrayCached(
                                    $out,
                                    $markerArray,
                                    array(),
                                    $wrappedSubpartContentArray
                                );
                        }
                    }

                    if (count($forumHeader) && !$lConf['noForums']) {
                            // Rendering forums
                        $c_forum = 0;
                        $contentModel =
                            GeneralUtility::makeInstance(
                                \JambageCom\TtBoard\Domain\Content::class
                            );
                        foreach($forums as $forumData) {
                            $contentRow = $contentModel->getRecord($forumData['uid']);
                            $out = $forumHeader[$c_forum % count($forumHeader)];
                            $c_forum++;
                            $forum_cObj->start($forumData);

                                // Clear
                            $markerArray = array();
                            $wrappedSubpartContentArray = array();

                                // Markers
                            $markerArray['###FORUM_TITLE###'] =
                                $forum_cObj->stdWrap(
                                    $markerObj->formatStr(
                                        $forumData['title']
                                    ),
                                    $lConf['forum_title_stdWrap.']
                                );

                            $markerArray['###FORUM_DESCRIPTION###'] =
                                $forum_cObj->stdWrap(
                                    $markerObj->formatStr(
                                        $forumData['subtitle']
                                    ),
                                    $lConf['forum_description_stdWrap.']
                                );

                            $pid = (
                                isset($contentRow) &&
                                is_array($contentRow) &&
                                $contentRow['pages'] ?
                                    $contentRow['pages'] :
                                    $forumData['uid']
                            );

                            $markerArray['###FORUM_POSTS###'] =
                                $forum_cObj->stdWrap(
                                    $modelObj->getNumPosts($pid),
                                    $lConf['forum_posts_stdWrap.']
                                );
                            $markerArray['###FORUM_THREADS###'] =
                                $forum_cObj->stdWrap(
                                    $modelObj->getNumThreads($pid),
                                    $lConf['forum_threads_stdWrap.']
                                );

                                // Link to the forum (wrap)
                            $pageLink =
                                FrontendUtility::getTypoLink_URL(
                                    $composite->getCObj(),
                                    $forumData['uid'],
                                    $linkParams,
                                    '',
                                    array('useCacheHash' => $allowCaching)
                                );

                            $wrappedSubpartContentArray['###LINK###'] =
                                array(
                                    '<a href="' . htmlspecialchars($pageLink) . '">',
                                    '</a>'
                                );

                                // LAST POST:
                            $lastPostInfo = $modelObj->getLastPost($pid);
                            $forum_cObj->start($lastPostInfo);

                            if ($lastPostInfo) {
                                $markerArray['###LAST_POST_AUTHOR###'] =
                                    $forum_cObj->stdWrap($markerObj->formatStr($lastPostInfo['author']), $lConf['last_post_author_stdWrap.']);
                                $markerArray['###LAST_POST_DATE###'] =
                                    $forum_cObj->stdWrap(
                                        $modelObj->recentDate(
                                            $lastPostInfo
                                        ),
                                        $conf['date_stdWrap.']
                                    );
                                $markerArray['###LAST_POST_TIME###'] =
                                    $forum_cObj->stdWrap(
                                        $modelObj->recentDate(
                                            $lastPostInfo
                                        ),
                                        $conf['time_stdWrap.']
                                    );
                                $markerArray['###LAST_POST_AGE###'] =
                                    $forum_cObj->stdWrap(
                                        $modelObj->recentDate($lastPostInfo),
                                        $conf['age_stdWrap.']
                                    );
                            } else {
                                $markerArray['###LAST_POST_AUTHOR###'] = '';
                                $markerArray['###LAST_POST_DATE###'] = '';
                                $markerArray['###LAST_POST_TIME###'] = '';
                                $markerArray['###LAST_POST_AGE###'] = '';
                            }

                                // Link to the last post
                            $overrulePIvars =
                                array_merge(
                                    $linkParams,
                                    array('uid' => $lastPostInfo['uid'])
                                );
                            $pageLink =
                                FrontendUtility::getTypoLink_URL(
                                    $composite->getCObj(),
                                    $contentRow['pid'],
                                    $overrulePIvars,
                                    '',
                                    array('useCacheHash' => $allowCaching)
                                );

                            $wrappedSubpartContentArray['###LINK_LAST_POST###'] =
                                array(
                                    '<a href="' . htmlspecialchars($pageLink) . '">',
                                    '</a>'
                                );

                            if (
                                version_compare(TYPO3_version, '8.7.0', '<')
                            ) {
                                    // Add result
                                $subpartContent .=
                                    $forum_cObj->substituteMarkerArrayCached(
                                        $out,
                                        $markerArray,
                                        array(),
                                        $wrappedSubpartContentArray
                                    );
                            } else {
                                    // Add result
                                $subpartContent .=
                                    $templateService->substituteMarkerArrayCached(
                                        $out,
                                        $markerArray,
                                        array(),
                                        $wrappedSubpartContentArray
                                    );
                            }

                                // Rendering the most recent posts
                            if (count($postHeader) && $lConf['numberOfRecentPosts']) {
                                $recentPosts =
                                    $modelObj->getMostRecentPosts(
                                        $forumData['uid'],
                                        intval($lConf['numberOfRecentPosts']),
                                        intval($lConf['numberOfRecentDays'])
                                    );

                                $c_post = 0;
                                foreach($recentPosts as $recentPost) {
                                    $out = $postHeader[$c_post % count($postHeader)];
                                    $c_post++;
                                    $forum_cObj->start($recentPost);

                                        // Clear:
                                    $markerArray = array();
                                    $wrappedSubpartContentArray = array();

                                        // markers:
                                    $markerArray['###POST_TITLE###'] =
                                        $forum_cObj->stdWrap(
                                            $markerObj->formatStr(
                                                $recentPost['subject']
                                            ),
                                            $lConf['post_title_stdWrap.']
                                        );
                                    $markerArray['###POST_CONTENT###'] =
                                        $markerObj->substituteEmoticons(
                                            $forum_cObj->stdWrap(
                                                $markerObj->formatStr(
                                                    $recentPost['message']
                                                ),
                                                $lConf['post_content_stdWrap.']
                                            )
                                        );
                                    $markerArray['###POST_REPLIES###'] =
                                        $forum_cObj->stdWrap(
                                            $modelObj->getNumReplies(
                                                $recentPost['pid'],
                                                $recentPost['uid']
                                            ),
                                            $lConf['post_replies_stdWrap.']
                                        );
                                    $markerArray['###POST_AUTHOR###'] =
                                        $forum_cObj->stdWrap(
                                            $markerObj->formatStr(
                                                $recentPost['author']
                                            ),
                                            $lConf['post_author_stdWrap.']
                                        );
                                    $markerArray['###POST_DATE###'] =
                                        $forum_cObj->stdWrap(
                                            $modelObj->recentDate(
                                                $recentPost
                                            ),
                                            $conf['date_stdWrap.']
                                        );
                                    $markerArray['###POST_TIME###'] =
                                        $forum_cObj->stdWrap(
                                            $modelObj->recentDate(
                                                $recentPost
                                            ),
                                            $conf['time_stdWrap.']
                                        );
                                    $markerArray['###POST_AGE###'] =
                                        $forum_cObj->stdWrap(
                                            $modelObj->recentDate(
                                                $recentPost
                                            ),
                                            $conf['age_stdWrap.']
                                        );

                                        // Link to the post:
                                    $forum_cObj->setCurrentVal($recentPost['pid']);
                                    $temp_conf = $composite->getTypolinkConf();
                                    $temp_conf['additionalParams'] .= '&tt_board_uid=' . $recentPost['uid'];
                                    $temp_conf['useCacheHash'] = $allowCaching;
                                    $temp_conf['no_cache'] = !$allowCaching;

                                    $wrappedSubpartContentArray['###LINK###'] =
                                        $forum_cObj->typolinkWrap($temp_conf);

                                    $overrulePIvars =
                                        array_merge(
                                            $linkParams,
                                            array('uid' => $recentPost['uid'])
                                        );
                                    $pageLink =
                                        FrontendUtility::getTypoLink_URL(
                                            $composite->getCObj(),
                                            $recentPost['pid'],
                                            $overrulePIvars,
                                            '',
                                            array('useCacheHash' => $allowCaching)
                                        );

                                    $wrappedSubpartContentArray['###LINK###'] =
                                        array(
                                            '<a href="' . htmlspecialchars($pageLink) . '">',
                                            '</a>'
                                        );

                                    if (
                                        version_compare(TYPO3_version, '8.7.0', '<')
                                    ) {
                                        $subpartContent .=
                                            $forum_cObj->substituteMarkerArrayCached(
                                                $out,
                                                $markerArray,
                                                array(),
                                                $wrappedSubpartContentArray
                                            );
                                    } else {
                                        $subpartContent .=
                                            $templateService->substituteMarkerArrayCached(
                                                $out,
                                                $markerArray,
                                                array(),
                                                $wrappedSubpartContentArray
                                            );
                                    }
                                }
                            }
                        }
                    }
                    if ($forumlist) {
                        break;
                    }
                }

                    // Substitution:
                $content .=
                    $templateService->substituteSubpart(
                        $templateCode,
                        '###CONTENT###',
                        $subpartContent
                    ) ;
            } else {
                $content = false;
                $composite->setErrorMessage(
                    'No template code for ###TEMPLATE_OVERVIEW###'
                );
            }
        }

        return $content;
    }
}
