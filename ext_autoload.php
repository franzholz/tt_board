<?php
$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

$key = 'tt_board';
$extensionPath = call_user_func($emClass . '::extPath', $key, $script);
return array(
    'JambageCom\\TtBoard\\Api\\Localization' => $extensionPath . 'Classes/Api/Localization.php',
    'JambageCom\\TtBoard\\Constants\\TreeMark' => $extensionPath . 'Classes/Constants/TreeMark.php',
    'JambageCom\\TtBoard\\Controller\\ActionController' => $extensionPath . 'Classes/Controller/ActionController.php',
    'JambageCom\\TtBoard\\Controller\\InitializationController' => $extensionPath . 'Classes/Controller/InitializationController.php',
    'JambageCom\\TtBoard\\Controller\\ListPluginController' => $extensionPath . 'Classes/Controller/ListPluginController.php',
    'JambageCom\\TtBoard\\Controller\\RegisterPluginController' => $extensionPath . 'Classes/Controller/RegisterPluginController.php',
    'JambageCom\\TtBoard\\Controller\\Submit' => $extensionPath . 'Classes/Controller/Submit.php',
    'JambageCom\\TtBoard\\Controller\\TreePluginController' => $extensionPath . 'Classes/Controller/TreePluginController.php',
    'JambageCom\\TtBoard\\Controller\\WizardIcon' => $extensionPath . 'Classes/Controller/WizardIcon.php',
    'JambageCom\\TtBoard\\Domain\\Composite' => $extensionPath . 'Classes/Domain/Composite.php',
    'JambageCom\\TtBoard\\Domain\\TtBoard' => $extensionPath . 'Classes/Domain/TtBoard.php',
    'JambageCom\\TtBoard\\View\\Forum' => $extensionPath . 'Classes/View/Forum.php',
    'JambageCom\\TtBoard\\View\\Marker' => $extensionPath . 'Classes/View/Marker.php',
    'JambageCom\\TtBoard\\View\\Tree => $extensionPath . 'Classes/View/Tree.php'
);


