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
 * Display of a post form
 *
 * @author  Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author  Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TtBoard\Domain\Composite;


class PostForm implements \TYPO3\CMS\Core\SingletonInterface {

    /**
    * Creates a post form for a forum
    */
    public function render (
        $theCode,
        $pid,
        $ref,
        array $linkParams,
        Composite $composite
    ) {
        $content = '';
        $conf = $composite->getConf();
        $modelObj = $composite->getModelObj();
        $languageObj = $composite->getLanguageObj();
        $local_cObj = \JambageCom\Div2007\Utility\FrontendUtility::getContentObjectRenderer();

        if (
            $modelObj->isAllowed($conf['memberOfGroups'])
        ) {
            $parent = 0;        // This is the parent item for the form. If this ends up being is set, then the form is a reply and not a new post.
            $nofity = array();

                // Find parent, if any
            if (
                $composite->getTtBoardUid() ||
                $ref != ''
            ) {
                if ($conf['tree']) {
                    $parent = $composite->getTtBoardUid();
                }

                $parentR =
                    $modelObj->getRootParent(
                        $composite->getTtBoardUid(),
                        $ref
                    );

                if (
                    is_array($parentR) &&
                    !$conf['tree']
                ) {
                    $parent = $parentR['uid'];
                }

                $wholeThread =
                    $modelObj->getSingleThread(
                        $parentR['uid'],
                        $ref,
                        1
                    );
                $notify = array();

                foreach($wholeThread as $recordP) { // the last notification checkbox will be supercede the previous settings

                    if ($recordP['email']) {

                        $index = md5(trim(strtolower($recordP['email'])));

                        if ($recordP['notify_me']) {
                            $notify[$index] = trim($recordP['email']);
                        } else if (!$recordP['notify_me']) {
                            if (isset($notify[$index])) {
                                unset($notify[$index]);
                            }
                        }
                    }
                }
            }

                // Get the render-code
            $lConf = $conf['postform.'];

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

            $setupArray =
                array(
                    '10' => 'subject',
                    '20' => 'message',
                    '30' => 'author',
                    '40' => 'email',
                    '50' => 'notify_me',
                    '60' => 'post_reply'
                );

            $modEmail = $conf['moderatorEmail'];
            if (!$parent && isset($conf['postform_newThread.'])) {
                $lConf = $conf['postform_newThread.'] ? $conf['postform_newThread.'] : $lConf;  // Special form for newThread posts...

                $modEmail = $conf['moderatorEmail_newThread'] ? $conf['moderatorEmail_newThread'] : $modEmail;
                $setupArray['60'] = 'post_new_reply';
            }

            if ($modEmail) {
                $modEmail = explode(',', $modEmail);
                foreach($modEmail as $modEmail_s) {
                    $notify[md5(trim(strtolower($modEmail_s)))] = trim($modEmail_s);
                }
            }

            if (
                $theCode == 'POSTFORM' ||
                ($theCode == 'POSTFORM_REPLY' && $parent) ||
                ($theCode == 'POSTFORM_THREAD' && !$parent)
            ) {
                $origRow = array();
                $wrongCaptcha = false;
                if (
                    isset($GLOBALS['TSFE']->applicationData) &&
                    is_array($GLOBALS['TSFE']->applicationData) &&
                    isset($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]) &&
                    is_array($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]) &&
                    isset($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error']) &&
                    is_array($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error'])
                ) {
                    if ($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error']['captcha'] == true) {
                        $origRow = $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['row'];
                        unset ($origRow['doublePostCheck']);
                        $wrongCaptcha = true;
                        $word = $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['word'];
                    }
                    if ($GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error']['spam'] == true) {
                        $spamWord = $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['word'];
                        $origRow = $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['row'];
                    }
                }

                if ($spamWord != '') {
                    $out =
                        sprintf(
                            $languageObj->getLL(
                                'spam_detected'
                            ),
                            $spamWord
                        );
                    $lConf['dataArray.']['1.'] = array(
                        'label' => 'ERROR !',
                        'type' => 'label',
                        'value' => $out,
                    );
                }
                $lConf['dataArray.']['9995.'] = array(
                    'type' => '*data[tt_board][NEW][prefixid]=hidden',
                    'value' => $composite->getPrefixId()
                );
                $lConf['dataArray.']['9996.'] = array(
                    'type' => '*data[tt_board][NEW][reference]=hidden',
                    'value' => $ref
                );
                $lConf['dataArray.']['9998.'] = array(
                    'type' => '*data[tt_board][NEW][pid]=hidden',
                    'value' => $pid
                );
                $lConf['dataArray.']['9999.'] = array(
                    'type' => '*data[tt_board][NEW][parent]=hidden',
                    'value' => $parent
                );

                if (
                    is_object(
                        $captcha = \JambageCom\Div2007\Captcha\CaptchaManager::getCaptcha(
                            TT_BOARD_EXT,
                            $conf['captcha']
                        )
                    )
                ) {
                    $captchaMarker = array();
                    $captcha->addGlobalMarkers(
                        $captchaMarker,
                        true
                    );
                    $textLabel = '';
                    if ($wrongCaptcha) {
                        $textLabel = '<b>' .
                            sprintf(
                                $languageObj->getLL(
                                    'wrong_captcha'
                                ),
                                $word
                            ) .
                            '</b><br/>';
                    }

                    if ($conf['captcha'] == 'freecap') {
                        $lConf['dataArray.']['55.'] = array(
                            'label' => $textLabel . $captchaMarker['###CAPTCHA_IMAGE###'] . '<br/>' . $captchaMarker['###CAPTCHA_NOTICE###'] . '<br/>' . $captchaMarker['###CAPTCHA_CANT_READ###'] . '<br/>' . $captchaMarker['###CAPTCHA_ACCESSIBLE###'],
                            'type' => '*data[tt_board][NEW][captcha]=input,60'
                        );
                    } else if ($conf['captcha'] == 'captcha') {
                        $lConf['dataArray.']['55.'] = array(
                            'label' => $textLabel . $captchaMarker['###CAPTCHA_IMAGE###'],
                            'type' => '*data[tt_board][NEW][captcha]=input,60'
                        );
                    }
                }

                if (count($notify)) {
                    $lConf['dataArray.']['9997.'] = array(
                        'type' => 'notify_me=hidden',
                        'value' => htmlspecialchars(implode($notify, ','))
                    );
                }

                if (is_array($GLOBALS['TSFE']->fe_user->user)) {
                    foreach ($lConf['dataArray.'] as $k => $dataRow) {
                        if (strpos($dataRow['type'], '[author]') !== false) {
                            $lConf['dataArray.'][$k]['value'] = $GLOBALS['TSFE']->fe_user->user['name'];
                        } else if (strpos($dataRow['type'],'[email]') !== false) {
                            $lConf['dataArray.'][$k]['value'] = $GLOBALS['TSFE']->fe_user->user['email'];
                        }
                    }
                }

                foreach ($setupArray as $k => $theField) {
                    if ($k == '60') {
                        $type = 'value';
                    } else {
                        $type = 'label';
                    }
                    if (is_array($lConf['dataArray.'][$k . '.'])) {
                        if (
                            (
                                !$languageObj->getLLkey() ||
                                $languageObj->getLLkey() == 'en'
                            ) &&
                            !$lConf['dataArray.'][$k . '.'][$type] ||

                            (
                                $languageObj->getLLkey() != 'en' &&
                                (
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']) ||
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']['lang.']) ||
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']['lang.'][$languageObj->getLLkey() . '.'])
                                )
                            )
                        ) {
                            $lConf['dataArray.'][$k . '.'][$type] =
                                $languageObj->getLL(
                                    $theField
                                );

                            if (
                                ($type == 'label') &&
                                isset($origRow[$theField])
                            ) {
                                $lConf['dataArray.'][$k . '.']['value'] = $origRow[$theField];
                            }
                        }
                    }
                }

                if ($composite->getTtBoardUid()) {
                    $linkParams[$composite->getPrefixId() . '[uid]'] = $composite->getTtBoardUid();
                }

                if (isset($linkParams) && is_array($linkParams)) {
                    $url =
                        \tx_div2007_alpha5::getPageLink_fh003(
                            $local_cObj,
                            $GLOBALS['TSFE']->id,
                            '',
                            $linkParams,
                            array('useCacheHash' => false)
                        );
                    $lConf['type'] = $url;
                }
                ksort($lConf['dataArray.']);
                $out = $local_cObj->cObjGetSingle('FORM', $lConf);
                $content .= $out;
            }
        }

        return $content;
    }
}


