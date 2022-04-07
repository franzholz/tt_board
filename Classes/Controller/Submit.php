<?php

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * See TSref document: boardLib.inc / FEDATA section for details on how to use this script.
 * The static template 'plugin.tt_board' provides a working example of configuration.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\TslibFetce\Controller\TypoScriptFrontendDataController;
use JambageCom\Div2007\Utility\MailUtility;
use JambageCom\TtBoard\Constants\Field;

class Submit implements \TYPO3\CMS\Core\SingletonInterface
{
    static public function execute (TypoScriptFrontendDataController $pObj, $conf)
    {
        $sanitizer = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Resource\FilePathSanitizer::class);
        $session = GeneralUtility::makeInstance(\JambageCom\TtBoard\Api\SessionHandler::class);
        $sessionData = $session->getSessionData();

        $result = true;
        $table = 'tt_board';
        $row = $pObj->newData[$table]['NEW'];

        // store the least entered row in order to allow a special output in the frontend
        $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['row'] = $row;

        $prefixId = $row['prefixid'];
        unset($row['prefixid']);
        $pid = intval($row['pid']);
        $local_cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $local_cObj->setCurrentVal($pid);
        $languageObj = GeneralUtility::makeInstance(\JambageCom\TtBoard\Api\Localization::class);
        $languageObj->init(
            TT_BOARD_EXT,
            $conf,
            DIV2007_LANGUAGE_SUBPATH
        );
        $languageObj->loadLocalLang(
            'EXT:' . TT_BOARD_EXT . DIV2007_LANGUAGE_SUBPATH . 'locallang.xlf',
            false
        );
        $allowCaching = $conf['allowCaching'] ? 1 : 0;
        if (is_array($row)) {
            $email = $row['email'];
        }
        $modelObj = GeneralUtility::makeInstance(\JambageCom\TtBoard\Domain\TtBoard::class);
        $modelObj->init();
        $allowed = $modelObj->isAllowed($conf['memberOfGroups']);

        if (
            $allowed &&
            (
                !$conf['emailCheck'] ||
                MailUtility::checkMXRecord($email)
            )
        ) {
            if (is_array($row) && trim($row['message'])) {
                do {
                    $internalFieldArray =
                        [
                            'hidden',
                            'parent',
                            'pid',
                            'reference',
                            'doublePostCheck',
                            Field::CAPTCHA
                        ];
                    $captchaError = false;

                    if (
                        isset($row[Field::CAPTCHA]) &&
                        $captcha =
                            \JambageCom\Div2007\Captcha\CaptchaManager::getCaptcha(
                                TT_BOARD_EXT,
                                $conf['captcha']
                            )
                    ) {
                        if (
                            !$captcha->evalValues(
                                $row[Field::CAPTCHA],
                                $conf['captcha']
                            )
                        ) {
                            $captchaError = true;
                        }
                    } else if ($conf['captcha']) {
                            // There could be a wrong captcha configuration or manipulation of the submit form. This case must always lead to an error message.
                        $captchaError = true;                        
                    }

                    if ($captchaError) {
                        $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error']['captcha'] = true;
                        $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['word'] = $row[Field::CAPTCHA];
                        $result = false;
                        break;
                    }

                    $spamArray = GeneralUtility::trimExplode(',', $conf['spamWords']);
                    $spamFound = false;

                    foreach ($row as $field => $value) {
                        if (!in_array($field, $internalFieldArray)) {
                            foreach ($spamArray as $k => $word) {
                                if ($word && stripos($value, $word) !== false) {
                                    $spamFound = true;
                                    break;
                                }
                            }
                        }
                        if ($spamFound) {
                            break;
                        }
                        $row[$field] = $value;
                    }

                    if ($spamFound) {
                        $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['error']['spam'] = true;
                        $GLOBALS['TSFE']->applicationData[TT_BOARD_EXT]['word'] = $word;
                        $result = false;
                        break;
                    } else {
                        $excludeArray = [];

                        if (version_compare(TYPO3_version, '10.0.0', '>=')) {
                            $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude'];
                        } else if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude.'])) {
                            $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_BOARD_EXT]['exclude.'];
                        }

                        if (
                            !GeneralUtility::inList(
                                $excludeArray[$table],
                                'cr_ip'
                            )
                        ) {                     
                            $row['cr_ip'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
                        }

                        if (isset($row[Field::CAPTCHA])) {
                            unset($row[Field::CAPTCHA]);
                        }

                            // Plain insert of record:
                        $newId = $pObj->execNEWinsert($table, $row);

                            // Link to this thread
                        $linkParams = [];
                        if ($GLOBALS['TSFE']->type) {
                            $linkParams['type'] = $GLOBALS['TSFE']->type;
                        }
                        $linkParams[$prefixId . '[uid]'] = $newId;
                        $url =
                            \JambageCom\Div2007\Utility\FrontendUtility::getTypoLink_URL(
                                $local_cObj,
                                $pid,
                                $linkParams,
                                '',
                                [
                                    'useCacheHash' => $allowCaching,
                                    'forceAbsoluteUrl' => 1
                                ]
                            );

                        $pObj->clear_cacheCmd($pid);
                       
                        \JambageCom\Div2007\Utility\SystemUtility::clearPageCacheContent_pidList($pid);
                        if ($pid != $GLOBALS['TSFE']->id) {
                            $pObj->clear_cacheCmd($GLOBALS['TSFE']->id);
                            \JambageCom\Div2007\Utility\SystemUtility::clearPageCacheContent_pidList(
                                $GLOBALS['TSFE']->id
                            );
                        }

                            // Clear specific cache:
                        if ($conf['clearCacheForPids']) {
                            $ccPids = GeneralUtility::intExplode(',', $conf['clearCacheForPids']);
                            foreach($ccPids as $ccPid) {
                                if ($ccPid > 0) {
                                    $pObj->clear_cacheCmd($ccPid);
                                }
                            }
                            $GLOBALS['TSFE']->clearPageCacheContent_pidList($conf['clearCacheForPids']);
                        }

                            // Send post to Mailing list ...
                        if (
                            $conf['sendToMailingList'] &&
                            $conf['sendToMailingList.']['email']
                        ) {
                        /*
                            TypoScript for this section (was used for the TYPO3 mailing list.
                        FEData.tt_board.processScript {
                            sendToMailingList = 1
                            sendToMailingList {
                                email = typo3@netfielders.de
                                reply = submitmail@typo3.com
                                namePrefix = Typo3Forum/
                                altSubject = Post from www.typo3.com
                            }
                        }
                        */
                            $mConf = $conf['sendToMailingList.'];

                            // If there is a FE-user group defined, then send notifiers to all FE-members of this group
                            if ($mConf['sendToFEgroup']) {
                                $sendToFEgroup = intval($mConf['sendToFEgroup']);
                                $feUserTable = 'fe_users';
                                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($feUserTable);
                                $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));

                                $statement =
                                    $queryBuilder
                                        ->select('*')
                                        ->from($feUserTable)
                                        ->orWhere(
                                            $queryBuilder->expr()->eq('usergroup', $queryBuilder->createNamedParameter($sendToFEgroup, \PDO::PARAM_STR)),
                                            $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter($sendToFEgroup . ',%', \PDO::PARAM_STR)),
                                            $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter('%,' . $sendToFEgroup, \PDO::PARAM_STR)),
                                            $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter('%,' . $sendToFEgroup . ',%', \PDO::PARAM_STR))
                                        )
                                        ->execute();
                                $c = 0;
                                while(
                                    $feRow = $statement->fetch()
                                ) {
                                    $c++;
                                    $emails .= $feRow['email'] . ',';
                                }
                                $maillist_recip = substr($emails, 0, -1);
                                // else, send to sendToMailingList.email
                            } else {
                                $maillist_recip = $mConf['email'];
                            }

                            $maillist_header='From: ' . $mConf['namePrefix'] . $row['author'] . ' <' . $mConf['reply'] . '>' . chr(10);
                            $maillist_header .= 'Reply-To: ' . $mConf['reply'];

                                //  Subject
                            if ($row['parent']) {	// RE:
                                $ttBoardTable = 'tt_board';
                                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($ttBoardTable);
                                $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));

                                $statement =
                                    $queryBuilder
                                        ->select('*')
                                        ->from($ttBoardTable)
                                        ->where(
                                            $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int) $row['parent'], \PDO::PARAM_INT))
                                        )
                                        ->execute();
                                $parentRow = $statement->fetch();
                                $maillist_subject = 'Re: ' . $parentRow['subject'] . ' [#' . $row['parent'] . ']';
                            } else {	// New:
                                $maillist_subject =  (trim($row['subject']) ? trim($row['subject']) : $mConf['altSubject']) . ' [#' . $newId . ']';
                            }

                                // Message
                            $maillist_msg = chr(10) . chr(10) . $languageObj->getLabel('newReply.subjectPrefix') .
                            chr(10) . $row['subject'] . chr(10) . chr(10) . $languageObj->getLabel('newReply.message') . chr(10) . $row['message'] . chr(10) . chr(10) . $languageObj->getLabel('newReply.author') . chr(10) . $row['author'] . chr(10) . chr(10) . chr(10);

                            $maillist_msg .= $languageObj->getLabel('newReply.followThisLink') . ':' . chr(10);
                            $maillist_msg .= $url;

                                // Send
                            if ($conf['debug']) {
                                debug($maillist_recip); // keep this
                                debug($maillist_subject); // keep this
                                echo nl2br($maillist_msg . chr(10));
                                debug($maillist_header); // keep this
                            } else {
                                $addresses = GeneralUtility::trimExplode(',', $maillist_recip);
                                foreach ($addresses as $email) {
                                    MailUtility::send(
                                        $email,
                                        $maillist_subject,
                                        $maillist_msg,
                                        '',
                                        $mConf['reply'],
                                        $mConf['namePrefix'] . $row['author']
                                    );
                                }
                            }
                        }

                        // Notify me...
                        $notify = false;
                        
                        if (
                            isset($sessionData['notify_me'])
                        ) {
                            $notify = implode(',', $sessionData['notify_me']);
                        }

                        if (
                            $notify &&
                            $conf['notify'] &&
                            trim($row['email']) &&
                            (
                                !$conf['emailCheck'] ||
                                MailUtility::checkMXRecord($row['email'])
                            )
                        ) {
                            $labelKeys = ['p_at', 'p_content', 'p_salutation', 'p_subject', 'p_text_snippet', 'p_url_title'];
                            $markersArray = [];
                            $markersArray['###AUTHOR###'] = trim($row['author']);
                            $markersArray['###AUTHOR_EMAIL###'] = trim($row['email']);
                            $markersArray['###AUTHOR_CITY###'] = trim($row['city']);
                            $markersArray['###CR_IP###'] = $row['cr_ip'];
                            $markersArray['###HOST###'] = GeneralUtility::getIndpEnv('HTTP_HOST');
                            $markersArray['###URL###'] = $url;

                            foreach ($labelKeys as $labelKey) {
                                $markersArray['###' . strtoupper($labelKey) . '###'] = $languageObj->getLabel($labelKey);
                            }
    
                            if ($row['parent']) {		// If reply and not new thread:
                                $absoluteFileName = $sanitizer->sanitize($conf['newReply.']['msg']);
                                $msg = GeneralUtility::getUrl($absoluteFileName);
                                $markersArray['###DID_WHAT###'] = $languageObj->getLabel('newReply.didWhat');
                                $markersArray['###SUBJECT_PREFIX###'] = $languageObj->getLabel('newReply.subjectPrefix');
                            } else {	// If new thread:
                                $absoluteFileName = $sanitizer->sanitize($conf['newThread.']['msg']);
                                $msg = GeneralUtility::getUrl($absoluteFileName);
                                $markersArray['###DID_WHAT###'] = $languageObj->getLabel('newThread.didWhat');
                                $markersArray['###SUBJECT_PREFIX###'] = $languageObj->getLabel('newThread.subjectPrefix');
                            }
                            $markersArray['###SUBJECT###'] = strtoupper($row['subject']);
                            $markersArray['###BODY###'] = GeneralUtility::fixed_lgd_cs($row['message'], 1000);

                            foreach($markersArray as $marker => $markContent) {
                                $msg = str_replace($marker, $markContent, $msg);
                            }

                            $headers = [];
                            if ($conf['notify_from']) {
                                $headers[] = 'FROM: ' . $conf['notify_from'];
                            }

                            $msgParts = explode(chr(10), $msg, 2);
                            $emailList = GeneralUtility::rmFromList($row['email'], $notify);

                            $notifyMe =
                                GeneralUtility::uniqueList(
                                    $emailList
                                );

                            if ($conf['debug']) {
                                debug($notifyMe); // keep this
                                debug($headers); // keep this
                                debug($msgParts); // keep this
                            } else {
                                $addresses = GeneralUtility::trimExplode(',', $notifyMe);
                                $senderArray =
                                    preg_split(
                                        '/(<|>)/',
                                        $conf['notify_from'],
                                        3,
                                        PREG_SPLIT_DELIM_CAPTURE
                                    );
                                $fromEmail = '';
                                if (count($senderArray) >= 4) {
                                    $fromEmail = $senderArray[2];
                                } else {
                                    $fromEmail = $senderArray[0];
                                }
                                $fromName = $senderArray[0];
                                foreach ($addresses as $email) {
                                    MailUtility::send(
                                        $email,
                                        $msgParts[0],
                                        $msgParts[1],
                                        '',
                                        $fromEmail,
                                        $fromName
                                    );
                                }
                            }
                        }
                    }
                } while (1 == 0);	// only once
            }
        } else {
            if ($allowed) {
                if ($email) {
                    $message = sprintf($languageObj->getLabel('error_email'), $email);
                } else {
                    $message = sprintf($languageObj->getLabel('error_no_email'), $email);
                }
            } else {
                $message = $languageObj->getLabel('error_no_permission');
            }

            $title = $languageObj->getLabel('error_access_denied');
            $errorController =
                GeneralUtility::makeInstance(
                    ErrorPageController::class
                );
            $content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
                $title,
                $message
            );
            $sessionData = [];
            $sessionData['error'] = $content;
            $session->setSessionData($sessionData);
        }

        return $result;
    }
}

