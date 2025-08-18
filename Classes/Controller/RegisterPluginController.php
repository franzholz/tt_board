<?php
declare(strict_types=1);

namespace JambageCom\TtBoard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger <franz@ttproducts.de>
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
 * Creates a forum/board in tree or list style
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use JambageCom\Div2007\Utility\ConfigUtility;

use JambageCom\TtBoard\Controller\InitializationController;
use JambageCom\TtBoard\Domain\Composite;

class RegisterPluginController
{
    protected ?ContentObjectRenderer $cObj = null;
    /**
     * Should be same as classname of the plugin, used for CSS classes, variables
     *
     * @var string
     */
    public $prefixId;
    /**
     * This is the incoming array by name $this->prefixId merged between POST and GET, POST taking precedence.
     * Eg. if the class name is 'tx_myext'
     * then the content of this array will be whatever comes into &tx_myext[...]=...
     *
     * @var array
     */
    public $piVars = [
        'pointer' => '',
        // Used as a pointer for lists
        'mode' => '',
        // List mode
        'sword' => '',
        // Search word
        'sort' => '',
    ];

    /**
     * Should normally be set in the main function with the TypoScript content passed to the method.
     *
     * $conf['LOCAL_LANG'][_key_] is reserved for Local Language overrides.
     * $conf['CODE.']['10.']['userFunc'] reserved for setting up the USER / USER_INT object. See TSref where CODE is the code of the plugin
     *
     * @var array
     */
    public $conf = [];
    public $extensionKey = 'tt_board';


    /**
     * This setter is called when the plugin is called from UserContentObject (USER)
     * via ContentObjectRenderer->callUserFunction().
     */
    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    /**
     * Returns the global arrays $_GET and $_POST merged with $_POST taking precedence.
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     * @return array Returns the GET vars merged recursively onto the POST vars.
     */
    private static function getRequestPostOverGetParameterWithPrefix(
        $parameter,
        ServerRequestInterface $request,
    )
    {
        $postParameter = $request->getParsedBody()[$parameter] ?? [];
        $postParameter = is_array($postParameter) ? $postParameter : [];
        $getParameter = $request->getQueryParams()[$parameter] ?? [];
        $getParameter = is_array($getParameter) ? $getParameter : [];
        $mergedParameters = $getParameter;
        ArrayUtility::mergeRecursiveWithOverrule($mergedParameters, $postParameter);
        return $mergedParameters;
    }

    /**
    * Main board function. Call this from TypoScript
    */
    public function main(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $this->conf = $conf;
        // Setting piVars:
        if ($this->prefixId) {
            $this->piVars =
                self::getRequestPostOverGetParameterWithPrefix(
                    $this->prefixId,
                    $request
                );
        }
        $codeArray = $this->getCodeArray($conf);
        $allowCaching = !empty($conf['allowCaching']) ? 1 : 0;
        foreach ($codeArray as $k => $theCode) {
            $theCode = (string) strtoupper(trim($theCode));

            if (
                !isset($conf['userFunc.']) ||
                !isset($conf['userFunc.'][$theCode])
            ) {
                continue;
            }
            $setupCode = $conf['userFunc.'][$theCode];
            $setup = $conf['userFunc.'][$theCode . '.'];
            $newConf = $conf;

            ArrayUtility::mergeRecursiveWithOverrule(
                $newConf,
                $setup['10.']
            );
            unset($newConf['userFunc.']);

            $contentObjectType = $setup['10'];
            if (
                !$allowCaching &&
                !strpos($contentObjectType, '_INT')
            ) {
                $contentObjectType .= '_INT';
            }

            $newSetup = [];
            $newSetup['10'] = $contentObjectType;
            $newSetup['10.'] = $newConf;
            $content .=
                $this->cObj->cObjGetSingle(
                    $setupCode,
                    $newSetup
                );
        }
        return $content;
    }

    public function init(
        string &$content,
        array $conf,
        ServerRequestInterface $request,
    ) : Composite {
        $initialization = GeneralUtility::makeInstance(
            InitializationController::class
        );
        $composite = null;
        $initialization->init(
            $composite,
            $content,
            $request,
            $this->cObj,
            $conf,
            $this->extensionKey,
            $this->piVars['uid'] ?? '',
            $this->prefixId
        );

        return $composite;
    }

    public function processCode(
        $theCode,
        &$content,
        Composite $composite,
        ServerRequestInterface $request,
    ): void {
        $action = GeneralUtility::makeInstance(
            ActionController::class
        );
        $action->processCode(
            $this->cObj,
            $theCode,
            $content,
            $composite,
            $request
        );
    }

    public function getCodeArray($conf)
    {
        $config = [];
        $codeArray = [];

        if (isset($this->cObj->data['pi_flexform'])) {
            $this->cObj->data['pi_flexform'] =
                GeneralUtility::xml2array($this->cObj->data['pi_flexform']);

            $config['code'] = ConfigUtility::getSetupOrFFvalue(
                $this->cObj,
                $conf['code'] ?? '',
                $conf['code.'] ?? '',
                $conf['defaultCode'] ?? '',
                $this->cObj->data['pi_flexform'],
                'display_mode',
                true
            );

            $codeArray = GeneralUtility::trimExplode(
                ',',
                $config['code'],
                true
            );
        }

        if (!count($codeArray)) {
            $codeArray = [''];
        }
        return ($codeArray);
    }

    public function help(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('HELP', $content, $composite, $request);
        return $content;
    }

    public function listCategories(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('LIST_CATEGORIES', $content, $composite, $request);
        return $content;
    }

    public function listForums(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('LIST_FORUMS', $content, $composite, $request);
        return $content;
    }

    public function forum(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('FORUM', $content, $composite, $request);
        return $content;
    }

    public function postForm(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM', $content, $composite, $request);
        return $content;
    }

    public function postFormReply(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM_REPLY', $content, $composite, $request);
        return $content;
    }

    public function thread(
        string $content,
        array $conf,
        ServerRequestInterface $request,
    ) : string {
        $composite = $this->init($content, $conf, $request);
        $this->processCode('POSTFORM_THREAD', $content, $composite, $request);
        return $content;
    }

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
