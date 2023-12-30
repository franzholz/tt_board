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
 * Display of a post form
 *
 * @author  Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author  Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use JambageCom\Div2007\Captcha\CaptchaManager;

use JambageCom\TtBoard\Constants\Field;
use JambageCom\TtBoard\Domain\Composite;

class Form implements \TYPO3\CMS\Core\SingletonInterface
{
    public function getPrivacyJavaScript($checkId, $buttonId)
    {
        $result = '
function addListeners() {
    if(window.addEventListener) {
        document.getElementById("' . $checkId . '").addEventListener("click", enableSubmitButtonFunc,false);
    } else if (window.attachEvent) { // Added For Internet Explorer versions previous to IE9
        document.getElementById("' . $checkId . '").attachEvent("onclick", enableSubmitButtonFunc);
    }

    function enableSubmitButtonFunc() {
        document.getElementById("' . $buttonId . '").disabled = !this.checked;
    }
}
window.onload = addListeners; 
        ';

        return $result;
    }

    /**
    * Creates a post form for a forum
    */
    public function render(
        ContentObjectRenderer $cObj,
        $theCode,
        $pid,
        $ref,
        array $linkParams,
        Composite $composite
    ) {
        $content = '';
        $session = GeneralUtility::makeInstance(\JambageCom\TtBoard\Api\SessionHandler::class);
        $currentSessionData = $session->getSessionData();
        $sessionData = [];
        $conf = $composite->getConf();
        $modelObj = $composite->getModelObj();
        $languageObj = $composite->getLanguageObj();
        $request = $cObj->getRequest();
        $uid = $composite->getTtBoardUid();
        $xhtmlFix = \JambageCom\Div2007\Utility\HtmlUtility::determineXhtmlFix();
        $useXhtml = \JambageCom\Div2007\Utility\HtmlUtility::useXHTML();
        $idPrefix = 'mailform';
        $extensionKey = $composite->getExtensionKey();
        $table = 'tt_board';
        $spamWord = '';
        $cssPrefix = 'tx-ttboard-';
        $notify = [];

        if (
            isset($GLOBALS['TSFE']->applicationData[$extensionKey]) &&
            is_array($GLOBALS['TSFE']->applicationData[$extensionKey]) &&
            !isset($GLOBALS['TSFE']->applicationData[$extensionKey]['error']) &&
            isset($GLOBALS['TSFE']->applicationData[$extensionKey]['row']) &&
            is_array($GLOBALS['TSFE']->applicationData[$extensionKey]['row'])
        ) {
            $content = $languageObj->getLabel(
                'post.thanks'
            );
        }

        if (
            $modelObj->isAllowed($conf['memberOfGroups'])
        ) {
            $parent = 0;        // This is the parent item for the form. If this is set, then the form is a reply and not a new post.
            $nofity = [];
            $feuserLoggedIn = false;

            if (
                is_array($GLOBALS['TSFE']->fe_user->user) &&
                (
                    isset($GLOBALS['TSFE']->fe_user->user['name']) ||
                    isset($GLOBALS['TSFE']->fe_user->user['email'])
                )
            ) {
                $feuserLoggedIn = true;
            }

            // Find parent, if any
            if (
                $uid ||
                $ref != ''
            ) {
                if ($conf['tree']) {
                    $parent = $uid;
                }
                $row =
                    $modelObj->getRootParent(
                        $uid,
                        $ref
                    );

                if (!$row) {
                    $row =
                        $modelObj->getCurrentPost(
                            $uid,
                            $ref
                        );
                }

                if (is_array($row)) {
                    if (
                        !$conf['tree']
                    ) {
                        $parent = $row['uid'];
                    }

                    $wholeThread =
                        $modelObj->getSingleThread(
                            $row['uid'],
                            $ref,
                            1,
                            false
                        );

                    foreach($wholeThread as $recordP) { // the last notification checkbox will be superceded by the previous settings

                        if ($recordP['email']) {

                            $index = md5(trim(strtolower($recordP['email'])));

                            if ($recordP['notify_me']) {
                                $notify[$index] = trim($recordP['email']);
                            } elseif (!$recordP['notify_me']) {
                                if (isset($notify[$index])) {
                                    unset($notify[$index]);
                                }
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
            //     50.label = Notify me<br>by reply:
            //     50.type = data[tt_board][NEW][notify_me]=check
            //     Captcha:
            //     55.label =
            //     Privacy Policy:
            //     60.label =
            //     Privacy checkbox
            //     61.label =
            //    300.type = formtype_db=submit
            //    300.value = Post Reply
            //   }

            $setupArray =
                [
                    '10' => 'subject',
                    '20' => 'message',
                    '30' => 'author',
                    '40' => 'email',
                    '50' => 'notify_me',
                    '300' => 'post_reply'
                ];

            $modEmail = $conf['moderatorEmail'];

            if (
                !$parent &&
                isset($conf['postform_newThread.'])
            ) {
                $lConf = $conf['postform_newThread.'] ? $conf['postform_newThread.'] : $lConf;  // Special form for newThread posts...

                $modEmail = $conf['moderatorEmail_newThread'] ? $conf['moderatorEmail_newThread'] : $modEmail;
                $setupArray['300'] = 'post_new';
            }
            if (!isset($lConf['params.'])) {
                $lConf['params.'] = [];
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
                $origRow = [];
                $wrongCaptcha = false;
                if (
                    isset($GLOBALS['TSFE']->applicationData[$extensionKey]) &&
                    is_array($GLOBALS['TSFE']->applicationData[$extensionKey]) &&
                    isset($GLOBALS['TSFE']->applicationData[$extensionKey]['error']) &&
                    is_array($GLOBALS['TSFE']->applicationData[$extensionKey]['error']) &&
                    isset($GLOBALS['TSFE']->applicationData[$extensionKey]['word'])
                ) {
                    if ($GLOBALS['TSFE']->applicationData[$extensionKey]['error']['captcha'] == true) {
                        $origRow = $GLOBALS['TSFE']->applicationData[$extensionKey]['row'];
                        unset($origRow['doublePostCheck']);
                        $wrongCaptcha = true;
                        $word = $GLOBALS['TSFE']->applicationData[$extensionKey]['word'];
                    }
                    if ($GLOBALS['TSFE']->applicationData[$extensionKey]['error']['spam'] == true) {
                        $spamWord = $GLOBALS['TSFE']->applicationData[$extensionKey]['word'];
                        $origRow = $GLOBALS['TSFE']->applicationData[$extensionKey]['row'];
                    }
                }

                if ($spamWord != '') {
                    $out =
                        sprintf(
                            $languageObj->getLabel(
                                'spam_detected'
                            ),
                            $spamWord
                        );
                    $lConf['dataArray.']['1.'] = [
                        'label' => 'ERROR !',
                        'type' => 'label',
                        'value' => $out,
                    ];
                }
                $lConf['dataArray.']['9995.'] = [
                    'type' => '*data[' . $table . '][NEW][prefixid]=hidden',
                    'value' => $composite->getPrefixId()
                ];
                $lConf['dataArray.']['9996.'] = [
                    'type' => '*data[' . $table . '][NEW][reference]=hidden',
                    'value' => $ref
                ];
                $lConf['dataArray.']['9998.'] = [
                    'type' => '*data[' . $table . '][NEW][pid]=hidden',
                    'value' => $pid
                ];
                $lConf['dataArray.']['9999.'] = [
                    'type' => '*data[' . $table . '][NEW][parent]=hidden',
                    'value' => $parent
                ];

                if (
                    is_object(
                        $captcha = CaptchaManager::getCaptcha(
                            $extensionKey,
                            $conf['captcha']
                        )
                    )
                ) {
                    $captchaMarker = [];
                    $textLabelWrap = '';
                    $markerFilled = $captcha->addGlobalMarkers(
                        $captchaMarker,
                        true
                    );
                    $textLabel =
                        $languageObj->getLabel(
                            'captcha'
                        );

                    if ($wrongCaptcha) {
                        $textLabelWrap = '<strong>' .
                            sprintf(
                                $languageObj->getLabel(
                                    'wrong_captcha'
                                ),
                                $word
                            ) .
                            '</strong><br'. $xhtmlFix . '>';
                    }

                    if (
                        $markerFilled
                    ) {
                        $additionalText = '';
                        if ($conf['captcha'] == 'freecap') {
                            $additionalText =
                                $captchaMarker['###CAPTCHA_CANT_READ###'] . '<br' . $xhtmlFix . '>' .
                                $captchaMarker['###CAPTCHA_ACCESSIBLE###'];
                        }
                        $lConf['dataArray.']['55.'] = [
                            'label' => $textLabel,
                            'label.' =>
                                [
                                    'wrap' =>
                                    '<span class="' . $cssPrefix . 'captcha">|' .
                                    $textLabelWrap .
                                    $captchaMarker['###CAPTCHA_IMAGE###']  . '<br' . $xhtmlFix . '>' .
                                    $captchaMarker['###CAPTCHA_NOTICE###'] . '<br' . $xhtmlFix . '>' .
                                    $additionalText . '</span>'
                                ],
                            'type' => '*data[' . $table . '][NEW][' . Field::CAPTCHA . ']=input,20'
                        ];
                    }
                } elseif (
                    isset($lConf['dataArray.']['55.']) &&
                    $lConf['dataArray.']['55.']['label'] == ''
                ) {
                    unset($lConf['dataArray.']['55.']);
                }

                if (
                    !$feuserLoggedIn &&
                    intval($conf['PIDprivacyPolicy'])
                ) {
                    $labelMap = [
                        'title' => 'privacy_policy.title',
                        'acknowledgement' => 'privacy_policy.acknowledgement',
                        'approval_required' => 'privacy_policy.approval_required',
                        'acknowledged' => 'privacy_policy.acknowledged',
                        'acknowledged_2' => 'privacy_policy.acknowledged_2',
                        'hint' => 'privacy_policy.hint',
                        'hint_1' => 'privacy_policy.hint_1'
                    ];

                    foreach ($labelMap as $key => $languageKey) {
                        $labels[$key] = $languageObj->getLabel($languageKey);
                    }
                    $piVars = [];

                    $pagePrivacy = intval($conf['PIDprivacyPolicy']);
                    $privacyUrl = $cObj->getTypoLink_URL($pagePrivacy, $piVars);
                    $privacyUrl = str_replace(['[', ']'], ['%5B', '%5D'], $privacyUrl);

                    $textLabelWrap = '<a href="' . htmlspecialchars($privacyUrl) . '">' . $labels['title'] . '</a><br' . $xhtmlFix . '>' . chr(13);
                    $lConf['dataArray.']['60.'] = [
                        'label' => $labels['title'] . ':',
                        'label.' =>
                            [
                                'wrap' =>
                                '<div class="'. $cssPrefix . 'privacy_policy"><strong>|</strong><br' . $xhtmlFix .'>' .
                                $textLabelWrap .
                                $labels['acknowledged_2'] . '<br' . $xhtmlFix . '>' .
                                '<strong>' . $labels['hint'] . '</strong><br' . $xhtmlFix . '>' .
                                $labels['hint_1'] . '</div>'
                            ],
                        'type' => 'label',
                        'value' =>  $labels['approval_required'],
                    ];

                    if (empty($request->getAttribute('privacy_policy'))) {
                        if (!isset($lConf['params.']['submit'])) {
                            $lConf['params.']['submit'] = '';
                        }
                        $lConf['params.']['submit'] .=
                            ($useXhtml ? ' disabled="disabled" ' : ' disabled ');
                    }

                    $lConf['dataArray.']['61.']['label'] = $labels['acknowledgement'];
                    $lConf['dataArray.']['61.']['label.'] =
                        [
                            'wrap' =>
                                '<span class="'. $cssPrefix . 'privacy_policy_checkbox">' .
                                $labels['acknowledged'] .
                                '</span>'
                        ];
                    $privacyJavaScript =
                        $this->getPrivacyJavaScript(
                            $idPrefix . 'privacypolicy',
                            $idPrefix . 'formtypedb'
                        );

                    GeneralUtility::makeInstance(AssetCollector::class)
                        ->addInlineJavaScript(
                            $extensionKey . '-privacy_policy',
                            $privacyJavaScript
                        );
                } else {
                    if (
                        isset($lConf['dataArray.']['60.']) &&
                        $lConf['dataArray.']['60.']['label'] == ''
                    ) {
                        unset($lConf['dataArray.']['60.']);
                    }
                    if (
                        isset($lConf['dataArray.']['61.']) &&
                        $lConf['dataArray.']['61.']['label'] == ''
                    ) {
                        unset($lConf['dataArray.']['61.']);
                    }
                }

                if (
                    $feuserLoggedIn
                ) {
                    foreach ($lConf['dataArray.'] as $k => $dataRow) {
                        if (strpos($dataRow['type'], '[author]') !== false) {
                            $lConf['dataArray.'][$k]['value'] = $GLOBALS['TSFE']->fe_user->user['name'];
                        } elseif (strpos($dataRow['type'], '[email]') !== false) {
                            $lConf['dataArray.'][$k]['value'] = $GLOBALS['TSFE']->fe_user->user['email'];
                        }
                    }
                }

                foreach ($setupArray as $k => $theField) {
                    if ($k == '300') {
                        $type = 'value';
                    } else {
                        $type = 'label';
                    }

                    if (
                        isset($lConf['dataArray.'][$k . '.']) &&
                        is_array($lConf['dataArray.'][$k . '.'])
                    ) {
                        if (
                            (
                                !$languageObj->getLocalLangKey() ||
                                $languageObj->getLocalLangKey() == 'default'
                            ) &&
                            !$lConf['dataArray.'][$k . '.'][$type] ||

                            (
                                $languageObj->getLocalLangKey() != 'default' &&
                                (
                                    isset($lConf['dataArray.'][$k . '.'][$type . '.']) &&
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']) ||
                                    isset($lConf['dataArray.'][$k . '.'][$type . '.']['lang.']) &&
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']['lang.']) ||
                                    isset($lConf['dataArray.'][$k . '.'][$type . '.']['lang.'][$languageObj->getLocalLangKey() . '.']) &&
                                    !is_array($lConf['dataArray.'][$k . '.'][$type . '.']['lang.'][$languageObj->getLocalLangKey() . '.'])
                                )
                            )
                        ) {
                            $lConf['dataArray.'][$k . '.'][$type] =
                                $languageObj->getLabel(
                                    $theField
                                );

                            if (
                                ($type == 'label')
                            ) {
                                if (isset($origRow[$theField])) {
                                    $lConf['dataArray.'][$k . '.']['value'] = $origRow[$theField];
                                } elseif (
                                    $theField == 'subject' &&
                                    $conf['fillSubject'] &&
                                    isset($row[$theField])
                                ) {
                                    $fillSubjectPrefix =
                                        $languageObj->getLabel(
                                            'post.fillSubjectPrefix'
                                        );

                                    $lConf['dataArray.'][$k . '.']['value'] = $fillSubjectPrefix . $row[$theField];
                                }
                            }
                        }
                    }
                }

                if ($composite->getTtBoardUid()) {
                    $linkParams[$composite->getPrefixId() . '[uid]'] = $composite->getTtBoardUid();
                }

                if (isset($linkParams) && is_array($linkParams)) {
                    $url =
                        \JambageCom\Div2007\Utility\FrontendUtility::getTypoLink_URL(
                            $cObj,
                            $GLOBALS['TSFE']->id,
                            $linkParams,
                            '',
                            []
                        );
                    $lConf['type'] = $url;
                }
                ksort($lConf['dataArray.']);
                $out = $cObj->cObjGetSingle('FORM', $lConf);
                $content .= $out;
            }
        }

        // delete any formerly stored values
        $GLOBALS['TSFE']->applicationData[$extensionKey] = [];

        if (!empty($notify)) {
            $sessionData['notify_me'] = $notify;
        }
        $session->setSessionData($sessionData);

        return $content;
    }
}
