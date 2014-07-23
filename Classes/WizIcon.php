<?php
namespace UniPassau\ImportStudip;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class WizIcon {

    /**
     * Processing the wizard items array
     *
     * @param    array $wizardItems : The wizard items
     *
     * @return    array Modified array with wizard items
     */
    function proc($wizardItems) {
        $wizardItems['plugins_tx_importstudip_pi1'] = array(
            'icon'        => ExtensionManagementUtility::extRelPath('importstudip') . '/Resources/Public/Images/Wizicon.png',
            'title'       => Tx_Extbase_Utility_Localization::translate('tx_importstudip.plugintitle'),
            'description' => Tx_Extbase_Utility_Localization::translate('tx_importstudip.plugindesc'),
            'params'      => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=importstudip_pi1'
        );

        return $wizardItems;
    }

}