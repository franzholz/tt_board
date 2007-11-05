<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2007 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * board_submit.inc
 *
 * See TSref document: boardLib.inc / FEDATA section for details on how to use this script.
 * The static template 'plugin.tt_board' provides a working example of configuration.
 *
 * $Id$
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 */

include_once (PATH_BE_ttboard.'lib/class.tx_ttboard_pibase.php');


if (is_object($this))	{
	$conf = $this->getConf('tt_board');
debug ($this->newData, '$this->newData', __LINE__, __FILE__);
	$row = $this->newData['tt_board']['NEW'];
debug ($row, '$row +++', __LINE__, __FILE__);
	if (is_array($row))	{
		$email = $row['email'];
	}
	$allowed = tx_ttboard_model::isAllowed($conf['memberOfGroups']);

	if ($allowed && (!$conf['emailCheck'] || checkEmail($email))) {

		if (is_array($row) && trim($row['message']))	{
			do {
				$spamArray = t3lib_div::trimExplode(',',$conf['spamWords']);
				$bSpamFound = false;
				$internalFieldArray = array('hidden','parent','pid','doublePostCheck', 'captcha');
				if ($conf['captcha'] == 'freecap' && t3lib_extMgm::isLoaded('sr_freecap'))	{
					require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
					$freeCapObj = &t3lib_div::getUserObj('&tx_srfreecap_pi2');
					if (!$freeCapObj->checkWord($row['captcha']))	{
						$content = 'Wrong captcha word entered';
						$GLOBALS['TSFE']->printError($content);
						break;
					}
				}

				foreach ($row as $field => $value)	{
					if (!in_array($field, $internalFieldArray))	{
						if (version_compare(phpversion(), '5.0.0', '>='))	{
							foreach ($spamArray as $k => $word)	{
								if ($word && stripos($value, $word) !== false)	{
									$bSpamFound = true;
									break;
								}
							}
						} else {
							foreach ($spamArray as $k => $word)	{
								$lWord = strtolower($word);
								$lValue = strtolower($value);
								if ($lWord && strpos($lValue, $lWord) !== false)	{
									$bSpamFound = true;
									break;
								}
							}
						}
					}
					if ($bSpamFound)	{
						break;
					}
				}
				if ($bSpamFound)	{
					$content = 'The spam word "'.$value.'" has been detected.';
					$GLOBALS['TSFE']->printError($content);
				} else {
					$row['cr_ip'] = t3lib_div::getIndpEnv('REMOTE_ADDR');

						// Plain insert of record:
					$this->execNEWinsert('tt_board', $row);
					$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();
					$this->clear_cacheCmd(intval($row['pid']));

						// Clear specific cache:
					if ($conf['clearCacheForPids'])	{
						$ccPids=t3lib_div::intExplode(',',$conf['clearCacheForPids']);
						reset($ccPids);
						while(list(,$pid)=each($ccPids))	{
							if ($pid > 0)	{$this->clear_cacheCmd($pid);}
						}
					}
						// Send post to Mailing list ...
					if ($conf['sendToMailingList'] && $conf['sendToMailingList.']['email'])	{
			/*
				TypoScript for this section (was used for the TYPO3 mailing list.

			sendToMailingList=1
			sendToMailingList {
			email = typo3@netfielders.de
			reply = submitmail@typo3.com
			namePrefix = Typo3Forum/
			altSubject = Post from www.typo3.com
			}
			*/
						$mConf = $conf['sendToMailingList.'];

						// If there is a FE-user group defined, then send notifiers to all FE-members of this group
						if ($mConf['sendToFEgroup'])	{
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'usergroup='.intval($mConf['sendToFEgroup']));
							$c = 0;
							while($feRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
								$c++;
								$emails .= $feRow['email'].',';
							}
							$GLOBALS['TYPO3_DB']->sql_free_result($res);
							$maillist_recip = substr($emails,0,-1);
							// else, send to sendToMailingList.email
						} else {
							$maillist_recip = $maillist_recip = $mConf['email'];
						}

						$maillist_header='From: '.$mConf['namePrefix'].$row['author'].' <'.$mConf['reply'].'>'.chr(10);
						$maillist_header.='Reply-To: '.$mConf['reply'];
	
							//  Subject
						if ($row['parent'])	{	// RE:
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_board', 'uid='.intval($row['parent']));
							$parentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
							$GLOBALS['TYPO3_DB']->sql_free_result($res);
							$maillist_subject = 'Re: '.$parentRow['subject'].' [#'.$row['parent'].']';
						} else {	// New:
							$maillist_subject =  (trim($row['subject']) ? trim($row['subject']) : $mConf['altSubject']).' [#'.$newId.']';
						}

							// Message
						$maillist_msg = chr(10).chr(10).$conf['newReply.']['subjectPrefix'].chr(10).$row['subject'].chr(10).chr(10).$conf['newReply.']['message'].chr(10).$row['message'].chr(10).chr(10).$conf['newReply.']['author'].chr(10).$row['author'].chr(10).chr(10).chr(10);
						$maillist_msg .= $conf['newReply.']['followThisLink'].chr(10).t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT').'?id='.$GLOBALS['TSFE']->id.'&type='.$GLOBALS['TSFE']->type.'&no_cache=1&tt_board_uid='.$newId;
							// Send

						if ($conf['debug'])	{
							debug($maillist_recip,1);
							debug($maillist_subject,1);
							echo nl2br($maillist_msg.chr(10));
							debug($maillist_header,1);
						} else {
							$addresses = explode(",", $maillist_recip);
							foreach ($addresses as $email) {
								mail ($email, $maillist_subject, $maillist_msg, $maillist_header);
							}
						}
					}

					// Notify me...
					if (t3lib_div::_GP('notify_me') && $conf['notify'])	{
						$notifyMe = t3lib_div::uniqueList(str_replace(','.$row['email'].',', ',', ','.t3lib_div::_GP('notify_me').','));
	
						$markersArray=array();
						$markersArray['###AUTHOR###'] = trim($row['author']);
						$markersArray['###AUTHOR_EMAIL###'] = trim($row['email']);
						$markersArray['###CR_IP###'] = $row['cr_ip'];
						$markersArray['###HOST###'] = t3lib_div::getIndpEnv('HTTP_HOST');
						$markersArray['###URL###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT').'?id='.$GLOBALS['TSFE']->id.'&type='.$GLOBALS['TSFE']->type.'&no_cache=1&tt_board_uid='.$newId;

						if ($row['parent'])	{		// If reply and not new thread:
							$msg = t3lib_div::getUrl($GLOBALS['TSFE']->tmpl->getFileName($conf['newReply.']['msg']));
							$markersArray['###DID_WHAT###']= $conf['newReply.']['didWhat'];
							$markersArray['###SUBJECT_PREFIX###']=$conf['newReply.']['subjectPrefix'];
						} else {	// If new thread:
							$msg = t3lib_div::getUrl($GLOBALS['TSFE']->tmpl->getFileName($conf['newThread.']['msg']));
							$markersArray['###DID_WHAT###']= $conf['newThread.']['didWhat'];
							$markersArray['###SUBJECT_PREFIX###']=$conf['newThread.']['subjectPrefix'];
						}
						$markersArray['###SUBJECT###'] = strtoupper($row['subject']);
						$markersArray['###BODY###'] = t3lib_div::fixed_lgd($row['message'],1000);

						reset($markersArray);
						while(list($marker,$markContent)=each($markersArray))	{
							$msg=str_replace($marker,$markContent,$msg);
						}

						$headers=array();
						if ($conf['notify_from'])	{
							$headers[]='FROM: '.$conf['notify_from'];
						}

						$msgParts = split(chr(10),$msg,2);
						if ($conf['debug'])	{
							debug($notifyMe,1);
							debug($headers,1);
							debug($msgParts);
						} else {
							$addresses = explode(",", $notifyMe);
							foreach ($addresses as $email) {
								mail ($email, $msgParts[0], $msgParts[1], implode($headers,chr(10)));
							}
						}
					}
				}
			} while (1 == 0);	// only once
		}
	} else {
		if ($allowed)	{
			$content = $email . ' is not a valid email address.';
		} else {
			$content = 'You have no permission to post into this forum!';
		}
		$GLOBALS['TSFE']->printError($content);
	}

}


// Added by Nicolas Liaudat
function checkEmail($email)	{

	if (!ereg('^[^@]{1,64}@[^@]{1,255}$', $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}

	// gets domain name
	list($username,$domain)=split('@',$email);
	// checks for if MX records in the DNS
	$mxhosts = array();
	if(!getmxrr($domain, $mxhosts))	{
		// no mx records, ok to check domain
		if (@fsockopen($domain,25,$errno,$errstr,30))	{
			return true;
		} else {
			return false;
		}
	} else {
		// mx records found
		foreach ($mxhosts as $host)	{
			if (@fsockopen($host,25,$errno,$errstr,30))	{
				return true;
			}
		}
		return false;
	}
}



?>