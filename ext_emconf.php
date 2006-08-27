<?php

########################################################################
# Extension Manager/Repository config file for ext: "tt_board"
#
# Auto generated 21-08-2006 09:46
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Message board, twin mode',
	'description' => 'Simple threaded (tree) or list message board',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Franz Holzinger',
	'author_email' => 'kontakt@fholzinger.com',
	'author_company' => 'Freelancer',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.11',
	'_md5_values_when_last_written' => 'a:28:{s:9:"ChangeLog";s:4:"cb41";s:28:"class.tx_ttboard_wizicon.php";s:4:"2c55";s:12:"ext_icon.gif";s:4:"4881";s:15:"ext_icon__h.gif";s:4:"d4dd";s:17:"ext_localconf.php";s:4:"061f";s:14:"ext_tables.php";s:4:"0352";s:14:"ext_tables.sql";s:4:"9695";s:9:"forum.gif";s:4:"7c8f";s:13:"locallang.php";s:4:"255e";s:25:"locallang_csh_ttboard.php";s:4:"1a19";s:17:"locallang_tca.php";s:4:"a4a6";s:17:"message_board.gif";s:4:"d36b";s:7:"tca.php";s:4:"c1fc";s:14:"doc/manual.sxw";s:4:"1a6b";s:20:"lib/board_submit.inc";s:4:"3967";s:31:"lib/class.tx_ttboard_pibase.php";s:4:"a278";s:28:"res/icons/fe/board_help1.gif";s:4:"1b80";s:24:"template/board_help.tmpl";s:4:"54b1";s:25:"template/board_notify.txt";s:4:"53f4";s:29:"template/board_template1.tmpl";s:4:"b849";s:29:"template/board_template2.tmpl";s:4:"d01f";s:29:"template/board_template3.tmpl";s:4:"71c3";s:30:"static/css_style/constants.txt";s:4:"872c";s:26:"static/css_style/setup.txt";s:4:"0e16";s:30:"static/old_style/constants.txt";s:4:"77d6";s:26:"static/old_style/setup.txt";s:4:"fdaf";s:36:"pi_list/class.tx_ttboard_pi_list.php";s:4:"12f7";s:36:"pi_tree/class.tx_ttboard_pi_tree.php";s:4:"848d";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '3.0.0-',
			'typo3' => '3.5.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'fh_library' => '0.0.11-',
		),
	),
);

?>