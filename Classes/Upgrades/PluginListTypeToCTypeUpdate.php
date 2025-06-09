<?php

declare(strict_types=1);

namespace JambageCom\TtBoard\Upgrades;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;


#[UpgradeWizard('ttBoardPluginListTypeToCTypeUpdate')]
final class PluginListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'tt_board_tree' => 'tt_board_tree',
            'tt_board_list' => 'tt_board_list',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrates tt_board plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates tt_board_tree and tt_board_list from list_type to CType.';
    }
}
