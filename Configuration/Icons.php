<?php
   return [
       'tt-board-tree' => [
           'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
           // The source bitmap file
           'source' => 'EXT:tt_board/Resources/Public/Icons/forum.gif',
           // All icon providers provide the possibility to register an icon that spins
           'spinning' => true,
       ],
       'tt-board-list' => [
           'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
           // The source bitmap file
           'source' => 'EXT:tt_board/Resources/Public/Icons/message_board.gif',
           // All icon providers provide the possibility to register an icon that spins
           'spinning' => true,
       ],
   ];
