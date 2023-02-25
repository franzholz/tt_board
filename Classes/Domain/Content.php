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


use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Api\Frontend;

/**
 * Function library for pages
 *
 * @author	Kasper Skårhøj  <kasperYYYY@typo3.com>
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


class Content implements \TYPO3\CMS\Core\SingletonInterface
{

    protected $tablename = 'tt_content';


    /**
    * Returns the content record
    */
    public function getRecord ($pid)
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $version = $typo3Version->getVersion();
        $result = null;

        $api =
            GeneralUtility::makeInstance(Frontend::class);
        $sys_language_uid = $api->getLanguageId();

        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($this->tablename);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));
        $queryBuilder->select('*')
            ->from($this->tablename)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT))
            )
            ->andWhere(
                $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter([2, 4], Connection::PARAM_INT_ARRAY))
            )
            ->andWhere(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid, \PDO::PARAM_INT))
            )
            ->setMaxResults(1);

        if (
            version_compare($version, '12.0.0', '>=') // Doctrine DBAL 3
        ) {
            $statement = $queryBuilder->executeQuery();
            $result = $statement->fetchAssociative();
        } else {
            $statement = $queryBuilder->execute();
            $result = $statement->fetch();
        }


        return $result;
    } //getRecord
}

