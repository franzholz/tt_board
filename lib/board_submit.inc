<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2007 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
	$email = $this->newData['tt_board']['NEW']['email'];
	$allowed = tx_ttboard_pibase::isAllowed($conf['memberOfGroups']);

	if ($allowed && (!$conf['emailCheck'] || checkEmail($email))) {

		if (is_array($this->newData['tt_board']['NEW']))	{

			$spamArray = t3lib_div::trimExplode(',',$conf['spamWords']);
			$bSpamFound = false;
			$internalFieldArray = array('hidden','parent','pid','doublePostCheck');

			foreach ($this->newData['tt_board']['NEW'] as $field => $value)	{
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
				$this->newData['tt_board']['NEW']['cr_ip'] = t3lib_div::getIndpEnv('REMOTE_ADDR');

					// Plain insert of record:
				$this->execNEWinsert('tt_board', $this->newData['tt_board']['NEW']);
				$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();
				$this->clear_cacheCmd(intval($this->newData['tt_board']['NEW']['pid']));

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
					$maillist_data = $this->newData['tt_board']['NEW'];

					// If there is a FE-user group defined, then send notifiers to all	FE-members of this group
					if ($mConf['sendToFEgroup'])	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'usergroup='.intval($mConf['sendToFEgroup']));
						$c = 0;
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							$c++;
							$emails .= $row['email'].',';
						}
						$maillist_recip = substr($emails,0,-1);
						// else, send to sendToMailingList.email
					} else {
						$maillist_recip = $maillist_recip = $mConf['email'];
					}

					$maillist_header='From: '.$mConf['namePrefix'].$maillist_data['author'].' <'.$mConf['reply'].'>'.chr(10);
					$maillist_header.='Reply-To: '.$mConf['reply'];

						//  Subject
					if ($maillist_data['parent'])	{	// RE:
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_board', 'uid='.intval($maillist_data['parent']));
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$maillist_subject = 'Re: '.$row['subject'].' [#'.$maillist_data['parent'].']';
					} else {	// New:
						$maillist_subject =  (trim($maillist_data['subject']) ? trim($maillist_data['subject']) : $mConf['altSubject']).' [#'.$newId.']';
					}

						// Message
					$maillist_msg = chr(10).chr(10).$conf['newReply.']['subjectPrefix'].chr(10).$maillist_data['subject'].chr(10).chr(10).$conf['newReply.']['message'].chr(10).$maillist_data['message'].chr(10).chr(10).$conf['newReply.']['author'].chr(10).$maillist_data['author'].chr(10).chr(10).chr(10);
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
					$notifyMe = t3lib_div::uniqueList(str_replace(','.$maillist_data['email'].',', ',', ','.t3lib_div::_GP('notify_me').','));

					$markersArray=array();
					$markersArray['###AUTHOR###'] = trim($this->newData['tt_board']['NEW'][author]);
					$markersArray['###AUTHOR_EMAIL###'] = trim($this->newData['tt_board']['NEW'][email]);
					$markersArray['###CR_IP###'] = $this->newData['tt_board']['NEW']['cr_ip'];
					$markersArray['###HOST###'] = t3lib_div::getIndpEnv('HTTP_HOST');
					$markersArray['###URL###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT').'?id='.$GLOBALS['TSFE']->id.'&type='.$GLOBALS['TSFE']->type.'&no_cache=1&tt_board_uid='.$newId;
		
					if ($maillist_data['parent'])	{		// If reply and not new thread:
						$msg = t3lib_div::getUrl($GLOBALS['TSFE']->tmpl->getFileName($conf['newReply.']['msg']));
						$markersArray['###DID_WHAT###']= $conf['newReply.']['didWhat'];
						$markersArray['###SUBJECT_PREFIX###']=$conf['newReply.']['subjectPrefix'];
					} else {	// If new thread:
						$msg = t3lib_div::getUrl($GLOBALS['TSFE']->tmpl->getFileName($conf['newThread.']['msg']));
						$markersArray['###DID_WHAT###']= $conf['newThread.']['didWhat'];
						$markersArray['###SUBJECT_PREFIX###']=$conf['newThread.']['subjectPrefix'];
					}
					$markersArray['###SUBJECT###'] = strtoupper($this->newData['tt_board']['NEW'][subject]);
					$markersArray['###BODY###'] = t3lib_div::fixed_lgd($this->newData['tt_board']['NEW'][message],1000);

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
						mail ($email, $msgParts[0], $msgParts[1],
							implode($headers,chr(10)));
						}
					}
				}
			}
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