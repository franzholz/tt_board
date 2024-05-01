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
 * Function library for a forum/board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author  Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author  Franz Holzinger <franz@ttproducts.de>
 */
use TYPO3\CMS\Core\SingletonInterface;
use JambageCom\TtBoard\Domain\TtBoard;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use JambageCom\Div2007\Utility\ExtensionUtility;

use JambageCom\TtBoard\Constants\TreeMark;

class Tree implements SingletonInterface
{
    private $dataModel;
    protected $treeIcons = [
        TreeMark::THREAD => '+',
        TreeMark::END => '-',
        TreeMark::JOIN_BOTTOM => '\\-',
        TreeMark::JOIN => '|-',
        TreeMark::LINE => '|&nbsp;',
        TreeMark::BLANK => '&nbsp;&nbsp;'
    ];

    protected $convertIconTypes = [
         TreeMark::THREAD => 'thread',
         TreeMark::END => 'end',
         TreeMark::JOIN_BOTTOM => 'joinBottom',
         TreeMark::JOIN => 'join',
         TreeMark::LINE => 'line',
         TreeMark::BLANK => 'blank'
     ];

    public function __construct(
        TtBoard $dataModel,
        array $iconConfig = []
    ) {
        $this->dataModel = $dataModel;

        $local_cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $joinConstants = array_keys($this->treeIcons);

        foreach ($joinConstants as $joinConstant) {
            $joinType = $this->convertIconTypes[$joinConstant];

            if (
                $joinType != '' &&
                isset($iconConfig[$joinType]) &&
                isset($iconConfig[$joinType . '.']) &&
                isset($iconConfig[$joinType . '.']['file'])
            ) {
                $config = $iconConfig[$joinType . '.'];
                $config['file'] =
                    ExtensionUtility::getExtensionFilePath(
                        $config['file'],
                        true
                    );
                $this->treeIcons[$joinConstant] =
                    $local_cObj->getContentObject(
                        $iconConfig[$joinType]
                    )->render(
                        $config
                    );
            }
        }
    }

    public function getDataModel()
    {
        return $this->dataModel;
    }

    public function getIcons()
    {
        return $this->treeIcons;
    }

    public function addTreeIcons(array &$rows): void
    {
        $icons = $this->getIcons();
        foreach ($rows as &$row) {
            $row['treeIcons'] = '';
            if (
                !empty($row['treeMarks'])
            ) {
                $treeMarks = explode(',', $row['treeMarks']);
                foreach ($treeMarks as $treeMark) {
                    if (isset($icons[$treeMark])) {
                        $row['treeIcons'] .= $icons[$treeMark];
                    }
                }
            }
        }
    }
}
