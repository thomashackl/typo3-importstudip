<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipConnector.php');

class ConfigForm {

    public function getExternConfigTypes($parameters, $config) {
        $result = '<div id="pagetypes">';
        $result .= '<script type="text/javascript" src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').'Resources/Public/JavaScript/tx_importstudip.js"></script>';
        $result .= '<style type="text/css">
            @import url("'.
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').
            'Resources/Public/Css/tx_importstudip.css")
            </style>';
        $types = StudipConnector::getExternConfigTypes();
        foreach ($types as $type) {
            $result .= '<input type="radio" id="' .$type[1].
                '" onclick="Tx_ImportStudip.getInstitutes()" name="'.
                $parameters['itemFormElName'].'" value="'.$type[1].'"/>'.
                '<label for="'.$type[1].'">'.$type[0].'</label><br/>';
        }
        $result .= '</div>';
        return $result;
    }

    public function getInstitutes($parameters, $config) {
        $result = '<div id="institutes" data-input-name="'.$parameters['itemFormElName'].'"></div>';
        $result .= '<script type="text/javascript">
        //<!--
        TYPO3.jQuery("#institutes").closest(".t3-form-field-container").hide();
        //-->
        </script>';
        return $result;
    }

    public function getExternConfigurations($parameters, $config) {
        $result = '<div id="externconfigs" data-input-name="'.$parameters['itemFormElName'].'"></div>';
        $result .= '<script type="text/javascript">
        //<!--
        TYPO3.jQuery("#externconfigs").closest(".t3-form-field-container").hide();
        //-->
        </script>';
        return $result;
    }

}
