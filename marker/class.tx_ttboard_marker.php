<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2007 Franz Holzinger <kontakt@fholzinger.com>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Part of the tt_board (Message Board) extension.
 *
 * marker functions
 *
 * $Id:$
 *
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @maintainer	Franz Holzinger <kontakt@fholzinger.com> 
 * @package TYPO3
 * @subpackage tt_products
 *
 *
 */



class tx_ttboard_marker {
	var $pibase;
	var $cObj;
	var $cnf;
	var $conf;
	var $config;
	var $urlArray;


	/**
	 * Initialized the marker object
	 *
	 */

	function init(&$pibase, &$conf, &$config)	{
 		$this->pibase = &$pibase;
 		$this->cObj = &$pibase->cObj;
 		$this->conf = &$conf;
 		$this->config = &$config;
	}


	/**
	 * getting the global markers
	 */
	function &getGlobalMarkers ()	{
		global $TYPO3_CONF_VARS;
		$markerArray = array();

			// globally substituted markers, fonts and colors.
		$splitMark = md5(microtime());
		list($markerArray['###GW1B###'],$markerArray['###GW1E###']) = explode($splitMark,$this->cObj->stdWrap($splitMark,$conf['wrap1.']));
		list($markerArray['###GW2B###'],$markerArray['###GW2E###']) = explode($splitMark,$this->cObj->stdWrap($splitMark,$conf['wrap2.']));
		list($markerArray['###GW3B###'],$markerArray['###GW3E###']) = explode($splitMark,$this->cObj->stdWrap($splitMark,$conf['wrap3.']));
		$markerArray['###GC1###'] = $this->cObj->stdWrap($this->conf['color1'],$this->conf['color1.']);
		$markerArray['###GC2###'] = $this->cObj->stdWrap($this->conf['color2'],$this->conf['color2.']);
		$markerArray['###GC3###'] = $this->cObj->stdWrap($this->conf['color3'],$this->conf['color3.']);
		$markerArray['###GC4###'] = $this->cObj->stdWrap($this->conf['color4'],$this->conf['color4.']);

		$markerArray['###PATH###'] = PATH_FE_ttboard_rel;
	
		if (is_array($this->conf['marks.']))	{
				// Substitute Marker Array from TypoScript Setup
			foreach ($this->conf['marks.'] as $key => $value)	{
				$markerArray['###'.$key.'###'] = $value;
			}
		}

			// Call all addURLMarkers hooks at the end of this method
		if (is_array ($TYPO3_CONF_VARS['EXTCONF'][TT_BOARD_EXTkey]['addGlobalMarkers'])) {
			foreach  ($TYPO3_CONF_VARS['EXTCONF'][TT_BOARD_EXTkey]['addGlobalMarkers'] as $classRef) {
				$hookObj= &t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, 'addGlobalMarkers')) {
					$hookObj->addGlobalMarkers($markerArray);
				}
			}
		}
		return $markerArray;	
	} // getGlobalMarkers


	function &getColumnMarkers ()	{
		$markerArray = array();

		$boardTextArray =
			array('author', 'date', 'forum', 'forum_list', 'go_to_top', 'threads', 'topics', 'last_post',
				'next_message', 'next_topic', 'previous_message', 'previous_topic', 'posts', 'search'
			);
		foreach ($boardTextArray as $k => $text)	{
			$markerArray['###BOARD_'.strtoupper($text).'###'] = $this->pibase->pi_getLL('board_'.$text);
		}
		$markerArray['###BUTTON_SEARCH###'] = $this->pibase->pi_getLL('button_search');

		return $markerArray;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_products/marker/class.tx_ttproducts_marker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_products/marker/class.tx_ttproducts_marker.php']);
}

?>
