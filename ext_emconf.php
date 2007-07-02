<?php

########################################################################
# Extension Manager/Repository config file for ext: "tt_board"
#
# Auto generated 03-06-2007 08:55
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
	'dependencies' => 'cms,fh_library',
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
	'version' => '1.1.9',
	'_md5_values_when_last_written' => 'a:34:{s:9:"ChangeLog";s:4:"2793";s:28:"class.tx_ttboard_wizicon.php";s:4:"3e44";s:16:"contributors.txt";s:4:"e2e4";s:21:"ext_conf_template.txt";s:4:"dbec";s:12:"ext_icon.gif";s:4:"4881";s:15:"ext_icon__h.gif";s:4:"d4dd";s:17:"ext_localconf.php";s:4:"d734";s:14:"ext_tables.php";s:4:"12a8";s:14:"ext_tables.sql";s:4:"4a67";s:23:"flexform_ds_pi_list.xml";s:4:"a333";s:23:"flexform_ds_pi_tree.xml";s:4:"2dde";s:9:"forum.gif";s:4:"7c8f";s:13:"locallang.php";s:4:"255e";s:25:"locallang_csh_ttboard.php";s:4:"1a19";s:17:"locallang_tca.php";s:4:"b925";s:17:"message_board.gif";s:4:"d36b";s:7:"tca.php";s:4:"5939";s:14:"doc/manual.sxw";s:4:"d41d";s:20:"lib/board_submit.inc";s:4:"feac";s:31:"lib/class.tx_ttboard_pibase.php";s:4:"8743";s:28:"res/icons/fe/board_help1.gif";s:4:"1b80";s:23:"res/icons/fe/thread.gif";s:4:"9aac";s:24:"template/board_help.tmpl";s:4:"ac9b";s:25:"template/board_notify.txt";s:4:"94a4";s:29:"template/board_template1.tmpl";s:4:"f508";s:29:"template/board_template2.tmpl";s:4:"f1be";s:29:"template/board_template3.tmpl";s:4:"71c3";s:30:"static/css_style/constants.txt";s:4:"5c53";s:26:"static/css_style/setup.txt";s:4:"1c79";s:30:"static/old_style/constants.txt";s:4:"b5c0";s:26:"static/old_style/setup.txt";s:4:"13bc";s:36:"pi_list/class.tx_ttboard_pi_list.php";s:4:"2865";s:36:"pi_tree/class.tx_ttboard_pi_tree.php";s:4:"e782";s:19:"share/locallang.xml";s:4:"65c6";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
			'fh_library' => '0.0.11-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>