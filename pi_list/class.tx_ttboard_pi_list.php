<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * boardLib.inc
 *
 * Creates a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * $Id$
 * 
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

require_once (PATH_BE_ttboard.'lib/class.tx_ttboard_pibase.php');

class tx_ttboard_pi_list extends tx_ttboard_pibase {

	/**
	 * Main board function. Call this from TypoScript
	 */
	function main($content,$conf)	{
		$codes=array();
		parent::init ($conf, $codes);
		while(list(,$theCode)=each($codes))	{
			$theCode = (string)strtoupper(trim($theCode));
			switch($theCode)	{
				case 'LIST_CATEGORIES':
				case 'LIST_FORUMS':
					$content.= $this->forum_list($theCode);
				break;
				case 'POSTFORM':
				case 'POSTFORM_REPLY':
				case 'POSTFORM_THREAD':
					$content.= $this->forum_postform($theCode);
				break;
				case 'FORUM':
				case 'THREAD_TREE':
					$content.= $this->forum_forum($theCode);
				break;
				default:
					$langKey = strtoupper($GLOBALS['TSFE']->config['config']['language']);
					$helpTemplate = $this->cObj->fileResource('EXT:tt_board/template/board_help.tmpl');

						// Get language version
					$helpTemplate_lang='';
					if ($langKey)	{$helpTemplate_lang = $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_'.$langKey.'###');}
					$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');

						// Markers and substitution:
					$markerArray['###CODE###'] = $theCode;
					$content.=$this->cObj->substituteMarkerArray($helpTemplate,$markerArray);
				break;
			}		// Switch
		}
		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/pi_list/class.tx_ttboard_pi_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_board/pi_list/class.tx_ttboard_pi_list.php']);
}

?>
