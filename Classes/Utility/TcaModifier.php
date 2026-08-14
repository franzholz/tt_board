<?php
namespace JambageCom\TtBoard\Utility;

defined('TYPO3') || die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Utility\TcaUtility;

use JambageCom\TtBoard\Constants\Extension;


class TcaModifier
{
    /**
     * Entfernt konfigurierte Felder automatisch aus dem übergebenen TCA
     */
    public static function removeExcludedFields(array &$tableTca, string $tableName): void
    {
        try {
            $excludedFieldsString = GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get(Extension::KEY, 'exclude/' . $tableName);

            if (is_string($excludedFieldsString) && trim($excludedFieldsString) !== '') {
                $fieldArray = GeneralUtility::trimExplode(',', $excludedFieldsString, true);

                if (!empty($fieldArray)) {
                    TcaUtility::removeField($tableTca, $fieldArray);
                }
            }
        } catch (\InvalidArgumentException $e) {
            // Sicheres Abfangen, falls die Konfiguration fehlt
        }
    }
}

