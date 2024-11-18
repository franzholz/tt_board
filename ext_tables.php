<?php

defined('TYPO3') || die('Access denied.');

call_user_func(function ($extensionKey): void {
    $table = 'tt_board';
    $languageSubpath = '/Resources/Private/Language/';
}, 'tt_board');
