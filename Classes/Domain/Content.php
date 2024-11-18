<?php

namespace JambageCom\TtBoard\Domain;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Api\Frontend;

/**
 * Function library for pages
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


class Content implements SingletonInterface
{
    protected $tablename = 'tt_content';


    /**
    * Returns the content record
    */
    public function getRecord($pid)
    {
        $result = null;

        $api =
            GeneralUtility::makeInstance(Frontend::class);
        $sys_language_uid = $api->getLanguageId();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tablename);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder->select('*')
            ->from($this->tablename)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT))
            )
            ->andWhere(
                $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter([2, 4], Connection::PARAM_INT_ARRAY))
            )
            ->andWhere(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid, Connection::PARAM_INT))
            )
            ->setMaxResults(1);

        $statement = $queryBuilder->executeQuery();
        $result = $statement->fetchAssociative();

        return $result;
    } //getRecord
}
