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
	var $prefixId = 'tx_ttboard_pi_list';	// Same as class name
	var $scriptRelPath = 'pi_list/class.tx_ttboard_pi_list.php';	// Path to this script relative to the extension dir.

	/**
	 * Main board function. Call this from TypoScript
	 */
	function main($content,$conf)	{
		$this->conf = $conf;

		parent::init ($content, $conf, $this->config);
		$codes=t3lib_div::trimExplode(',', $this->config['code'],1);
		if (!count($codes))	$codes=array('');

		while(!$this->errorMessage && list(,$theCode)=each($codes))	{
			$theCode = (string)strtoupper(trim($theCode));
			switch($theCode)	{
				default:
					parent::processCode($theCode, $content);
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
