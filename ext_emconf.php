<?php

########################################################################
# Extension Manager/Repository config file for ext: "tt_board"
#
# Auto generated 18-09-2008 20:49
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Message board, twin mode',
	'description' => 'Simple threaded (tree) or list message board.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.2.6',
	'dependencies' => 'cms,div2007',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Franz Holzinger',
	'author_email' => 'contact@fholzinger.com',
	'author_company' => 'jambage.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
			'div2007' => '0.1.15-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:38:{s:9:"ChangeLog";s:4:"6ce2";s:28:"class.tx_ttboard_wizicon.php";s:4:"3e44";s:16:"contributors.txt";s:4:"e2e4";s:21:"ext_conf_template.txt";s:4:"7997";s:12:"ext_icon.gif";s:4:"4881";s:15:"ext_icon__h.gif";s:4:"d4dd";s:17:"ext_localconf.php";s:4:"07a7";s:14:"ext_tables.php";s:4:"12a8";s:14:"ext_tables.sql";s:4:"4a67";s:23:"flexform_ds_pi_list.xml";s:4:"a333";s:23:"flexform_ds_pi_tree.xml";s:4:"2dde";s:9:"forum.gif";s:4:"7c8f";s:13:"locallang.php";s:4:"255e";s:25:"locallang_csh_ttboard.php";s:4:"1a19";s:17:"locallang_tca.php";s:4:"b925";s:17:"message_board.gif";s:4:"d36b";s:7:"tca.php";s:4:"5939";s:14:"doc/manual.sxw";s:4:"f93b";s:20:"lib/board_submit.php";s:4:"1dac";s:31:"lib/class.tx_ttboard_pibase.php";s:4:"e9c7";s:28:"res/icons/fe/board_help1.gif";s:4:"1b80";s:23:"res/icons/fe/thread.gif";s:4:"9aac";s:31:"view/class.tx_ttboard_forum.php";s:4:"922f";s:36:"hooks/class.tx_ttboard_hooks_cms.php";s:4:"72c3";s:32:"model/class.tx_ttboard_model.php";s:4:"4bb7";s:19:"share/locallang.xml";s:4:"f288";s:24:"template/board_help.tmpl";s:4:"ac9b";s:25:"template/board_notify.txt";s:4:"a9ea";s:29:"template/board_template1.tmpl";s:4:"906a";s:29:"template/board_template2.tmpl";s:4:"4865";s:29:"template/board_template3.tmpl";s:4:"30f5";s:34:"marker/class.tx_ttboard_marker.php";s:4:"4e00";s:30:"static/css_style/constants.txt";s:4:"35f6";s:26:"static/css_style/setup.txt";s:4:"1a04";s:30:"static/old_style/constants.txt";s:4:"9b1b";s:26:"static/old_style/setup.txt";s:4:"83bf";s:36:"pi_list/class.tx_ttboard_pi_list.php";s:4:"0764";s:36:"pi_tree/class.tx_ttboard_pi_tree.php";s:4:"1ba9";}',
	'suggests' => array(
	),
);

?>