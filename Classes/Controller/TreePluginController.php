<?php
declare(strict_types=1);

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger <franz@ttproducts.de>
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
 * Creates a forum/board in tree style
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use JambageCom\Div2007\Utility\ConfigUtility;

use JambageCom\TtBoard\Controller\InitializationController;
use JambageCom\TtBoard\Domain\Composite;


class TreePluginController extends RegisterPluginController
{
    /**
     * @var string
     */
    protected $prefixId = 'tt_board_tree';


    #[AsAllowedCallable]
    public function help(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('HELP', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function listCategories(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('LIST_CATEGORIES', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function listForums(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('LIST_FORUMS', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function forum(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('FORUM', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function postForm(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function postFormReply(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM_REPLY', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function thread(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM_THREAD', $content, $composite, $request);
        return $content;
    }

    #[AsAllowedCallable]
    public function threadTree(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('THREAD_TREE', $content, $composite, $request);
        return $content;
    }
}

