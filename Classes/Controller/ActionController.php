<?php

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * ActionController
 *
 * Function library for a forum / board in tree or list style
 *
 * TypoScript config:
 * - See static_template 'plugin.tt_board_tree' and plugin.tt_board_list
 * - See TS_ref.pdf
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use JambageCom\TtBoard\Domain\Composite;

class ActionController implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
    * Returns a message, formatted
    */
    static public function outMessage ($string, $content = '')
    {
        $msg = '
        <hr>
        <h3>' . $string . '</h3>
        ' . $content . '
        <hr>
        ';

        return $msg;
    }

    public function processCode (
        ContentObjectRenderer $cObj,
        $theCode,
        &$content,
        Composite $composite
    )
    {
        $conf = $composite->getConf();
        $contentTmp = '';
        $ref = (isset($conf['ref']) ? $conf['ref'] : ''); // reference is set if another TYPO3 extension has a record which references to its own forum
        $linkParams = (isset($conf['linkParams.']) ? $conf['linkParams.'] : []);

        switch($theCode) {
            case 'LIST_CATEGORIES':
            case 'LIST_FORUMS':
                $forumList =
                    GeneralUtility::makeInstance(
                        \JambageCom\TtBoard\View\ForumList::class
                    );

                $newContent =
                    $forumList->render(
                        $theCode,
                        $composite,
                        $linkParams
                    );
            break;
            case 'POSTFORM':
            case 'POSTFORM_REPLY':
            case 'POSTFORM_THREAD':
                $pidArray =
                    GeneralUtility::trimExplode(
                        ',',
                        $composite->getPidList()
                    );
                $pid = $pidArray[0];
                $form =
                    GeneralUtility::makeInstance(
                        \JambageCom\TtBoard\View\Form::class
                    );
                $newContent =
                    $form->render(
                        $theCode,
                        $pid,
                        $ref,
                        $linkParams,
                        $composite
                    );
            break;
            case 'FORUM':
            case 'THREAD_TREE':
                $pid = ($conf['PIDforum'] ? $conf['PIDforum'] : $GLOBALS['TSFE']->id);
                $treeView = null;

                if ($conf['tree']) {
                    $treeView =
                        GeneralUtility::makeInstance(
                            \JambageCom\TtBoard\View\Tree::class,
                            $composite->getModelObj(),
                            $conf['iconCode.']
                        );
                }
                $uid = $composite->getTtBoardUid();

                if (
                    (
                        $uid ||
                        $ref != ''
                    ) &&
                    $theCode == 'FORUM'
                ) {
                    $view = GeneralUtility::makeInstance(\JambageCom\TtBoard\View\ForumThread::class);
                    $newContent =
                        $view->printView(
                            $composite,
                            $treeView,
                            $conf,
                            $ref,
                            $theCode,
                            $linkParams,
                            $pid
                        );
                } else {
                    $view = GeneralUtility::makeInstance(\JambageCom\TtBoard\View\Forum::class);
                    $newContent =
                        $view->printView(
                            $composite,
                            $treeView,
                            $conf,
                            $ref,
                            $theCode,
                            $linkParams,
                            $pid
                        );
                }
            break;
            default:
                $contentTmp = 'error';
            break;
        }	// Switch

        if ($content === false) {
            $this->outMessage($composite->getErrorMessage());
        } else if ($contentTmp == 'error') {
            $fileName = 'EXT:' . $composite->getExtensionKey() . '/Resources/Private/Templates/board_help.tmpl';
            $sanitizer = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Resource\FilePathSanitizer::class);
            $absoluteFileName = $sanitizer->sanitize($fileName);
            $helpTemplate = file_get_contents($absoluteFileName);
            $newContent = \JambageCom\Div2007\Utility\ViewUtility::displayHelpPage(
                $composite->getLanguageObj(),
                $composite->getCObj(),
                $helpTemplate,
                $composite->getExtensionKey(),
                $composite->getErrorMessage(),
                $theCode
            );
            $composite->setErrorMessage('');
        }
        $content .= $newContent;
        return true;
    }
}

