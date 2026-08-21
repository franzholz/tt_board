<?php

declare(strict_types=1);

namespace JambageCom\TtBoard\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;
use TYPO3\CMS\Core\Upgrades\RepeatableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[UpgradeWizard('ttBoard_pluginListTypeToCTypeUpdate')]
final class PluginListTypeToCTypeUpdate implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE_CONTENT = 'tt_content';
    private const TABLE_BACKEND_USER_GROUPS = 'be_groups';

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * Maps the old list_type identifiers to the new modern CType identifiers.
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            '2' => 'ttboard_tree',
            '4' => 'ttboard_list',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrates tt_board plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates tt_board tree and list from list_type 2 and 4 to modern CType structures.';
    }

    /**
     * Strictly required by the UpgradeWizardInterface in TYPO3 13/14.
     *
     * @return array<class-string<\TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface>>
     */
    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * Safe schema check for TYPO3 13/14 using createSchemaManager().
     */
    protected function columnsExistInContentTable(): bool
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_CONTENT);
        $columns = array_keys($connection->createSchemaManager()->listTableColumns(self::TABLE_CONTENT));

        return in_array('ctype', $columns, true) && in_array('list_type', $columns, true);
    }

    protected function columnsExistInBackendUserGroupsTable(): bool
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_BACKEND_USER_GROUPS);
        $columns = array_keys($connection->createSchemaManager()->listTableColumns(self::TABLE_BACKEND_USER_GROUPS));

        return in_array('explicit_allowdeny', $columns, true);
    }

    /**
     * Main check wrapper for TYPO3 Upgrade system.
     */
    public function updateNecessary(): bool
    {
        return $this->hasContentElementsToUpdate() || $this->hasBackendUserGroupsToUpdate();
    }

    protected function hasContentElementsToUpdate(): bool
    {
        if (!$this->columnsExistInContentTable()) {
            return false;
        }

        $listTypesToUpdate = array_keys($this->getListTypeToCTypeMapping());
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_CONTENT);
        $queryBuilder->getRestrictions()->removeAll();

        $count = (int)$queryBuilder
        ->count('uid')
        ->from(self::TABLE_CONTENT)
        ->where(
            $queryBuilder->expr()->eq('ctype', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->in(
                    'list_type',
                    $queryBuilder->createNamedParameter($listTypesToUpdate, Connection::PARAM_STR_ARRAY)
                )
        )
        ->executeQuery()
        ->fetchOne();

        return $count > 0;
    }

    protected function hasBackendUserGroupsToUpdate(): bool
    {
        if (!$this->columnsExistInBackendUserGroupsTable() || $this->hasNoLegacyBackendGroupsExplicitAllowDenyConfiguration()) {
            return false;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_BACKEND_USER_GROUPS);
        $queryBuilder->getRestrictions()->removeAll();

        $searchConstraints = [];
        foreach ($this->getListTypeToCTypeMapping() as $listType => $contentType) {
            $searchConstraints[] = $queryBuilder->expr()->like(
                'explicit_allowdeny',
                $queryBuilder->createNamedParameter(
                    '%' . $queryBuilder->escapeLikeWildcards('tt_content:list_type:' . $listType) . '%'
                )
            );
        }

        $count = (int)$queryBuilder
        ->count('uid')
        ->from(self::TABLE_BACKEND_USER_GROUPS)
        ->where($queryBuilder->expr()->or(...$searchConstraints))
        ->executeQuery()
        ->fetchOne();

        return $count > 0;
    }

    protected function hasNoLegacyBackendGroupsExplicitAllowDenyConfiguration(): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_BACKEND_USER_GROUPS);
        $queryBuilder->getRestrictions()->removeAll();

        $count = (int)$queryBuilder
        ->count('uid')
        ->from(self::TABLE_BACKEND_USER_GROUPS)
        ->where(
            $queryBuilder->expr()->like('explicit_allowdeny', $queryBuilder->createNamedParameter('%ALLOW%'))
        )
        ->executeQuery()
        ->fetchOne();

        return $count === 0;
    }

    /**
     * Main execution wrapper required by TYPO3 Upgrade system.
     */
    public function executeUpdate(): bool
    {
        $this->updateContentElements();
        $this->updateBackendUserGroups();
        return true;
    }

    protected function getContentElementsToUpdate(string|int $listType): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_CONTENT);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
        ->select('uid')
        ->from(self::TABLE_CONTENT)
        ->where(
            $queryBuilder->expr()->eq('ctype', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter((string)$listType))
        )
        ->executeQuery()
        ->fetchAllAssociative();
    }

    protected function getBackendUserGroupsToUpdate(string|int $listType): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_BACKEND_USER_GROUPS);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
        ->select('uid', 'explicit_allowdeny')
        ->from(self::TABLE_BACKEND_USER_GROUPS)
        ->where(
            $queryBuilder->expr()->like(
                'explicit_allowdeny',
                $queryBuilder->createNamedParameter(
                    '%' . $queryBuilder->escapeLikeWildcards('tt_content:list_type:' . $listType) . '%'
                )
            )
        )
        ->executeQuery()
        ->fetchAllAssociative();
    }

    protected function updateContentElements(): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_CONTENT);

        foreach ($this->getListTypeToCTypeMapping() as $listType => $contentType) {
            foreach ($this->getContentElementsToUpdate($listType) as $record) {
                $connection->update(
                    self::TABLE_CONTENT,
                    [
                        'ctype' => $contentType,
                        'list_type' => '',
                    ],
                    ['uid' => (int)$record['uid']]
                );
            }
        }
    }

    protected function updateBackendUserGroups(): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_BACKEND_USER_GROUPS);

        foreach ($this->getListTypeToCTypeMapping() as $listType => $contentType) {
            foreach ($this->getBackendUserGroupsToUpdate($listType) as $record) {
                $fields = GeneralUtility::trimExplode(',', $record['explicit_allowdeny'], true);
                foreach ($fields as $key => $field) {
                    if ($field === 'tt_content:list_type:' . $listType) {
                        unset($fields[$key]);
                        $fields[] = 'tt_content:CType:' . $contentType;
                    }
                }

                $connection->update(
                    self::TABLE_BACKEND_USER_GROUPS,
                    [
                        'explicit_allowdeny' => implode(',', array_unique($fields)),
                    ],
                    ['uid' => (int)$record['uid']]
                );
            }
        }
    }
}
