<?php

namespace JambageCom\TtBoard\Api;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Franz Holzinger (franz@ttproducts.de)
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
 * API object
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_board
 *
 *
 */

 
class Api implements \TYPO3\CMS\Core\SingletonInterface {
    /**
    * Retrieves default configuration of tt_board.
    * Uses plugin.tt_board_list or plugin.tt_board_tree from page TypoScript template
    *
    * @param	string type of the forum: list or tree
    *
    * @return	array/bool  TypoScript configuration 
    */
    public function getDefaultConfig ($type) {
        $result = false;
        if ($type == 'list' || $type == 'tree') {
            $key = 'tt_board_' . $type . '.';
            if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.'][$key])) {
                $result = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$key];
            }
        }
        return $result;
    }
}

