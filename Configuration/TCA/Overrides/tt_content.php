<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['4'] = 'layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['4'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['2'] = 'layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['2'] = 'pi_flexform';

