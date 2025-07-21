<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tt-board-tree' => [
        'provider' => BitmapIconProvider::class,
        // The source bitmap file
        'source' => 'EXT:tt_board/Resources/Public/Icons/forum.gif'
    ],
    'tt-board-list' => [
        'provider' => BitmapIconProvider::class,
        // The source bitmap file
        'source' => 'EXT:tt_board/Resources/Public/Icons/message_board.gif'
    ],
];
