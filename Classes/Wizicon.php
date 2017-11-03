<?php
namespace UniPassau\Importstudip;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Wizicon {

    /**
     * Processing the wizard items array
     *
     * @param    array $wizardItems : The wizard items
     *
     * @return    array Modified array with wizard items
     */
    function proc($wizardItems) {
        $wizardItems['plugins_tx_importstudip_pi1'] = array(
            'title' => LocalizationUtility::translate('plugintitle', 'importstudip'),
            'description' => LocalizationUtility::translate('plugindesc', 'importstudip'),
            'params' => '&defVals[tt_content][CType]=list'
        );

        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
            $wizardItems['plugins_tx_importstudip_pi1']['icon'] =
                ExtensionManagementUtility::extRelPath('importstudip') . 'Resources/Public/Icons/Extension.svg';
        } else {
            $wizardItems['plugins_tx_importstudip_pi1']['icon'] =
                ExtensionManagementUtility::extRelPath('importstudip') . 'Resources/Public/Icons/Extension.png';
        }

        return $wizardItems;
    }

}
