<?php
namespace UniPassau\ImportStudip;

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
            'icon'        => ExtensionManagementUtility::extRelPath('importstudip') . 'Resources/Public/Images/Wizicon.png',
            'title'       => LocalizationUtility::translate('plugintitle', 'importstudip'),
            'description' => LocalizationUtility::translate('plugindesc', 'importstudip'),
            'params'      => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=importstudip_pi1'
        );

        return $wizardItems;
    }

}
