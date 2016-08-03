<?php

$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

if (
	class_exists($emClass) &&
	method_exists($emClass, 'extPath')
) {
	// nothing
} else {
	$emClass = 't3lib_extMgm';
}

$key = 'tt_board';

$extensionPath = call_user_func($emClass . '::extPath', $key, $script);

return array(
	'tx_ttboard_api'     => $extensionPath . 'api/class.tx_ttboard_api.php',
	'tx_ttboard_pi_list' => $extensionPath . 'pi_list/class.tx_ttboard_pi_list.php',
	'tx_ttboard_pi_tree' => $extensionPath . 'pi_list/class.tx_ttboard_pi_tree.php',
	'tx_ttboard_pibase'  => $extensionPath . 'lib/class.tx_ttboard_pibase.php',
	'tx_ttboard_marker'  => $extensionPath . 'marker/class.tx_ttboard_marker.php',
	'tx_ttboard_model'   => $extensionPath . 'model/class.tx_ttboard_model.php',
	'tx_ttboard_forum'   => $extensionPath . 'view/class.tx_ttboard_forum.php',
	'tx_ttboard_wizicon' => $extensionPath . 'class.tx_ttboard_wizicon.php',
);

