<?php

namespace JambageCom\TtBoard\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

 /**
 * See TSref document: boardLib.inc / FEDATA section for details on how to use this script.
 * The static template 'plugin.tt_board' provides a working example of configuration.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;

use JambageCom\Div2007\Captcha\CaptchaManager;
use JambageCom\Div2007\Utility\FrontendUtility;
use JambageCom\Div2007\Utility\MailUtility;
use JambageCom\Div2007\Utility\SystemUtility;

use JambageCom\TslibFetce\Controller\TypoScriptFrontendDataController;

use JambageCom\TtBoard\Api\Localization;
use JambageCom\TtBoard\Api\SessionHandler;
use JambageCom\TtBoard\Constants\Field;
use JambageCom\TtBoard\Domain\TtBoard;


class Submit implements SingletonInterface
{
    public static function execute(TypoScriptFrontendDataController $pObj, $conf)
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $version = $typo3Version->getVersion();
        $sanitizer = GeneralUtility::makeInstance(FilePathSanitizer::class);
        $session = GeneralUtility::makeInstance(SessionHandler::class);
        $sessionData = $session->getSessionData();

        $modelObj = GeneralUtility::makeInstance(TtBoard::class);
        $modelObj->init();
        $allowed = $modelObj->isAllowed($conf['memberOfGroups']);

        $result = true;
        $pid = 0;
        $extensionKey = 'tt_board';
        $table = 'tt_board';
        $languageSubpath = '/Resources/Private/Language/';
        $row = $pObj->newData[$table]['NEW'] ?? null;

        if (isset($row)) {
            // store the least entered row in order to allow a special output in the frontend
            $GLOBALS['TSFE']->applicationData[$extensionKey]['row'] = $row;

            if (isset($row['prefixid'])) {
                $prefixId = $row['prefixid'];
                unset($row['prefixid']);
            }
            $pid = intval($row['pid']);
            $local_cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $local_cObj->setCurrentVal($pid);
            $languageObj = GeneralUtility::makeInstance(Localization::class);
            $languageObj->init(
                $extensionKey,
                $conf,
                $languageSubpath
            );
            $languageObj->loadLocalLang(
                'EXT:' . $extensionKey . $languageSubpath . 'locallang.xlf',
                false
            );
            if (is_array($row)) {
                $email = $row['email'] ?? '';
            }
        }

        if (
            $allowed &&
            (
                empty($conf['emailCheck']) ||
                MailUtility::checkMXRecord($email)
            )
        ) {
            if (isset($row) && is_array($row) && trim($row['message'])) {
                do {
                    $internalFieldArray =
                        [
                            'hidden',
                            'parent',
                            'pid',
                            'reference',
                            'doublePostCheck',
                            Field::CAPTCHA,
                            Field::SLUG
                        ];
                    $captchaError = false;

                    if (
                        isset($row[Field::CAPTCHA]) &&
                        null !== (
                            $captcha =
                                CaptchaManager::getCaptcha(
                                    $extensionKey,
                                    $conf['captcha']
                            )
                        ) &&
                        is_object($captcha)
                    ) {
                        if (
                            !$captcha->evalValues(
                                $row[Field::CAPTCHA],
                                $conf['captcha']
                            )
                        ) {
                            $captchaError = true;
                        }
                    } elseif (!empty($conf['captcha'])) {
                        // There could be a wrong captcha configuration or manipulation of the submit form. This case must always lead to an error message.
                        $captchaError = true;
                    }

                    if ($captchaError) {
                        $GLOBALS['TSFE']->applicationData[$extensionKey]['error']['captcha'] = true;
                        $GLOBALS['TSFE']->applicationData[$extensionKey]['word'] = $row[Field::CAPTCHA] ?? '*';
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
                    }

                    if ($spamFound) {
                        $GLOBALS['TSFE']->applicationData[$extensionKey]['error']['spam'] = true;
                        $GLOBALS['TSFE']->applicationData[$extensionKey]['word'] = $word;
                        $result = false;
                        break;
                    } else {
                        $excludeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]['exclude'];

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

                        $tcaConfig = $GLOBALS['TCA'][$table]['columns']['slug']['config'];
                        $helper =
                            GeneralUtility::makeInstance(
                                SlugHelper::class,
                                $table,
                                Field::SLUG,
                                $tcaConfig
                            );
                        $slug =
                            $helper->generate(
                                $row,
                                $pid
                            );
                        $row[Field::SLUG] = $slug;

                        // Plain insert of record:
                        $newId = $pObj->execNEWinsert($table, $row);

                        // Link to this thread
                        $linkParams = [];
                        if ($GLOBALS['TSFE']->type) {
                            $linkParams['type'] = $GLOBALS['TSFE']->type;
                        }
                        $linkParams[$prefixId . '[uid]'] = $newId;
                        $url =
                            FrontendUtility::getTypoLink_URL(
                                $local_cObj,
                                $pid,
                                $linkParams,
                                '',
                                [
                                    'forceAbsoluteUrl' => 1
                                ]
                            );
                        $pObj->clear_cacheCmd($pid);
                        SystemUtility::clearPageCacheContent_pidList($pid);

                        if ($pid != $GLOBALS['TSFE']->id) {
                            $pObj->clear_cacheCmd($GLOBALS['TSFE']->id);
                            SystemUtility::clearPageCacheContent_pidList(
                                $GLOBALS['TSFE']->id
                            );
                        }

                        // Clear specific cache:
                        if (!empty($conf['clearCacheForPids'])) {
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
                            $mConf = $conf['sendToMailingList.'] ?? [];

                            // If there is a FE-user group defined, then send notifiers to all FE-members of this group
                            if (!empty($mConf['sendToFEgroup'])) {
                                $sendToFEgroup = intval($mConf['sendToFEgroup']);
                                $feUserTable = 'fe_users';
                                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($feUserTable);
                                $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

                                $queryBuilder
                                    ->select('*')
                                    ->from($feUserTable)
                                    ->orWhere(
                                        $queryBuilder->expr()->eq('usergroup', $queryBuilder->createNamedParameter($sendToFEgroup, \PDO::PARAM_STR)),
                                        $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter($sendToFEgroup . ',%', \PDO::PARAM_STR)),
                                        $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter('%,' . $sendToFEgroup, \PDO::PARAM_STR)),
                                        $queryBuilder->expr()->like('usergroup', $queryBuilder->createNamedParameter('%,' . $sendToFEgroup . ',%', \PDO::PARAM_STR))
                                    );

                                if (
                                    version_compare($version, '12.0.0', '>=') // Doctrine DBAL 3
                                ) {
                                    $statement = $queryBuilder->executeQuery();
                                } else {
                                    $statement = $queryBuilder->execute();
                                }

                                $c = 0;
                                while(
                                    $feRow = (version_compare($version, '12.0.0', '>=') ? $statement->fetchAssociative() : $statement->fetch())
                                ) {
                                    $c++;
                                    $emails .= $feRow['email'] . ',';
                                }
                                $maillist_recip = substr($emails, 0, -1);
                                // else, send to sendToMailingList.email
                            } else {
                                $maillist_recip = $mConf['email'];
                            }

                            $maillist_header = 'From: ' . $mConf['namePrefix'] . $row['author'] . ' <' . $mConf['reply'] . '>' . chr(10);
                            $maillist_header .= 'Reply-To: ' . $mConf['reply'];

                            //  Subject
                            if (!empty($row['parent'])) {
                                $queryBuilder =
                                    GeneralUtility::makeInstance(ConnectionPool::class)->
                                        getQueryBuilderForTable($table);
                                $queryBuilder->setRestrictions(
                                    GeneralUtility::makeInstance(
                                        FrontendRestrictionContainer::class)
                                    );

                                $queryBuilder
                                    ->select('*')
                                    ->from($table)
                                    ->where(
                                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int) $row['parent'], \PDO::PARAM_INT))
                                    );

                                if (
                                    version_compare($version, '12.0.0', '>=') // Doctrine DBAL 3
                                ) {
                                    $statement = $queryBuilder->executeQuery();
                                    $parentRow = $statement->fetchAssociative();
                                } else {
                                    $statement = $queryBuilder->execute();
                                    $parentRow = $statement->fetch();
                                }

                                $maillist_subject = 'Re: ' . $parentRow['subject'] . ' [#' . $row['parent'] . ']';
                            } else {	// New:
                                $maillist_subject =  (trim($row['subject']) ?: $mConf['altSubject']) . ' [#' . $newId . ']';
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
                            !empty($row['email'])
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
                            $email = $row['email'];
                            $emailList = implode(',', array_filter(explode(',', $notify), function ($item) use ($email) {
                                return $email == $item;
                            }));

                            $notifyMe =
                                StringUtility::uniqueList($emailList);

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
                                    if (
                                        empty($conf['emailCheck']) ||
                                        MailUtility::checkMXRecord($email)
                                    ) {
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
                    }
                } while (1 == 0);	// Execute this loop only once.
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
            $sessionData['error-title'] = $title;
            $sessionData['error-message'] = $message;
            $session->setSessionData($sessionData);
        }

        return $result;
    }
}
