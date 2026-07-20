<?php

declare(strict_types=1);

namespace JambageCom\TtBoard\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

final class BoardPageTitleProvider extends AbstractPageTitleProvider
{
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}

